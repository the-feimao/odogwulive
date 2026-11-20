<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\AppUser;
use App\Models\Review;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Setting;
use App\Models\OrderTax;
use App\Models\OrderChild;
use App\Models\User;
use App\Models\Settlement;
use App\Models\PaymentSetting;
use Stripe;
use QrCode;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Group a report aggregate by the supplied key so report pages do not run
     * one query per row.
     */
    private function groupedAggregate($query, string $key, string $expression)
    {
        return $query->select($key)
            ->selectRaw($expression.' as total')
            ->groupBy($key)
            ->pluck('total', $key);
    }

    /**
     * Prepare review rows for the admin page without touching API appends.
     */
    private function prepareReviewRows($reviews): void
    {
        $reviews->each->setAppends([]);
        $userIds = $reviews->pluck('user_id')->filter()->unique()->values();
        $eventIds = $reviews->pluck('event_id')->filter()->unique()->values();

        $users = $userIds->isNotEmpty()
            ? AppUser::whereIn('id', $userIds)->get(['id', 'name', 'last_name', 'image', 'email'])->keyBy('id')
            : collect();
        $events = $eventIds->isNotEmpty()
            ? Event::whereIn('id', $eventIds)->get(['id', 'name'])->keyBy('id')
            : collect();

        foreach ($reviews as $review) {
            $review->reviewer = $users->get($review->user_id);
            $review->eventName = $events->get($review->event_id)?->name;
        }
    }

    /**
     * Attach settlement owners with one user query for all rows.
     */
    private function attachSettlementUsers($settlements): void
    {
        $userIds = $settlements->pluck('user_id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        foreach ($settlements as $settlement) {
            $settlement->user = $users->get($settlement->user_id);
        }
    }
    private function authorizeOrderAccess(Order $order): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('organization') && (int) $order->organization_id === (int) $user->id) {
            return;
        }

        abort(403);
    }

    public function index()
    {
        if (Auth::user()->hasRole('admin')) {
            $orders = Order::with(['customer', 'event'])->OrderBy('id', 'DESC')->get();
        } elseif (Auth::user()->hasRole('organization')) {
            $orders = Order::with(['customer', 'event'])->where('organization_id', Auth::user()->id)->OrderBy('id', 'DESC')->get();
        } else {
            abort(403);
        }

        return view('admin.order.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'event', 'organization', 'ticket'])->findOrFail($id);
        $this->authorizeOrderAccess($order);

        return view('admin.order.view', compact('order'));
    }

    public function orderInvoice($id)
    {
        $order = Order::with(['customer', 'event', 'organization', 'ticket'])->findOrFail($id);
        $this->authorizeOrderAccess($order);

        $order->tax_data = OrderTax::with('tax')->where('order_id', $order->id)->get();
        $order->ticket_data = OrderChild::where('order_id', $order->id)->get();

        return view('admin.order.invoice', compact('order'));
    }

    public function userReview(){
        $data = Review::orderBy('id','DESC')->get();
        $this->prepareReviewRows($data);
        return view('admin.review', compact('data'));
    }

    public function changeReviewStatus($id){
        Review::find($id)->update(['status'=>1]);
        return redirect()->back()->withStatus(__('Review is published successfully.'));
    }

    public function deleteReview($id){
        $data = Review::find($id);
        $data->delete();
        return redirect()->back()->withStatus(__('Review is deleted successfully.'));
    }

    public function customerReport(Request $request){
        $data = AppUser::orderBy('id','DESC');
        if(isset($request->duration) && $request->duration != null){
            $start_date = explode(' to ',$request->duration)[0];
            $end_date = count(explode(' to ',$request->duration))==1?explode(' to ',$request->duration)[0]:explode(' to ',$request->duration)[1];
            $data->whereBetween('created_at', [ $start_date." 00:00:00", $end_date." 23:59:59"]);
        }
        $data = $data->get();

        $buyTickets = $this->groupedAggregate(
            Order::where('organization_id', Auth::user()->id)->whereIn('customer_id', $data->pluck('id')),
            'customer_id',
            'SUM(quantity)'
        );
        foreach ($data as $value) {
            $value->buy_tickets = (int) ($buyTickets->get($value->id) ?? 0);
        }
        return view('admin.report.org_customer_report', compact('data','request'));
    }

    public function ordersReport(Request $request){
        $data = Order::where([['organization_id',Auth::user()->id],['payment_status',1]]);
        if(isset($request->customer) && $request->customer >= 1){
            $data->where('customer_id',$request->customer);
        }
        if(isset($request->duration) && $request->duration != null){
            $start_date = explode(' to ',$request->duration)[0];
            $end_date = count(explode(' to ',$request->duration))==1?explode(' to ',$request->duration)[0]:explode(' to ',$request->duration)[1];
            $data->whereBetween('created_at', [ $start_date." 00:00:00", $end_date." 23:59:59"]);
        }

        $data = $data->with([
            'customer:id,name,last_name,email',
            'event:id,name,start_time',
        ])->orderBy('id','DESC')->get();
        $customers = AppUser::orderBy('id','DESC')->get();

        return view('admin.report.org_orders_report', compact('data','request','customers'));
    }

    public function orgRevenueReport(Request $request){
        $data = Settlement::where('user_id',Auth::user()->id)->orderBy('id','DESC');
        if(isset($request->duration) && $request->duration != null){
            $start_date = explode(' to ',$request->duration)[0];
            $end_date = count(explode(' to ',$request->duration))==1?explode(' to ',$request->duration)[0]:explode(' to ',$request->duration)[1];
            $data->whereBetween('created_at', [ $start_date." 00:00:00", $end_date." 23:59:59"]);
        }
        $data = $data->get();

        $this->attachSettlementUsers($data);

        return view('admin.report.org_revenue', compact('data','request'));
    }

    public function adminCustomerReport(Request $request){
        $data = AppUser::orderBy('id','DESC');

        if(isset($request->duration) && $request->duration != null){
            $start_date = explode(' to ',$request->duration)[0];
            $end_date = count(explode(' to ',$request->duration))==1?explode(' to ',$request->duration)[0]:explode(' to ',$request->duration)[1];
            $data->whereBetween('created_at', [ $start_date." 00:00:00", $end_date." 23:59:59"]);
        }
        $data = $data->get();
        $buyTickets = $this->groupedAggregate(
            Order::whereIn('customer_id', $data->pluck('id')),
            'customer_id',
            'SUM(quantity)'
        );
        foreach ($data as $value) {
            $value->buy_tickets = (int) ($buyTickets->get($value->id) ?? 0);
        }
        return view('admin.report.admin_customer_report', compact('data','request'));
    }

    public function adminOrgReport(Request $request){
        $data = User::role('organization')->orderBy('id','DESC');
        if(isset($request->duration) && $request->duration != null){
            $start_date = explode(' to ',$request->duration)[0];
            $end_date = count(explode(' to ',$request->duration))==1?explode(' to ',$request->duration)[0]:explode(' to ',$request->duration)[1];
            $data->whereBetween('created_at', [ $start_date." 00:00:00", $end_date." 23:59:59"]);
        }
        $data = $data->get();
        $organizationIds = $data->pluck('id');
        $eventCounts = $this->groupedAggregate(
            Event::whereIn('user_id', $organizationIds),
            'user_id',
            'COUNT(*)'
        );
        $ticketTotals = $this->groupedAggregate(
            Ticket::where('is_deleted', 0)->whereIn('user_id', $organizationIds),
            'user_id',
            'SUM(quantity)'
        );
        $soldTotals = $this->groupedAggregate(
            Order::whereIn('organization_id', $organizationIds),
            'organization_id',
            'SUM(quantity)'
        );
        foreach ($data as $value) {
            $value->total_events = (int) ($eventCounts->get($value->id) ?? 0);
            $value->total_tickets = (int) ($ticketTotals->get($value->id) ?? 0);
            $value->sold_tickets = (int) ($soldTotals->get($value->id) ?? 0);
        }
        return view('admin.report.admin_org_report', compact('data','request'));
    }

    public function adminRevenueReport(Request $request){
        $data = Order::with(['customer:id,name,last_name,email','event:id,name'])->where('payment_status',1);
        if(isset($request->organizer) && $request->organizer >= 1){
            $data->where('organization_id',$request->organizer);
        }
        if(isset($request->customer) && $request->customer >= 1){
              $data->where('customer_id',$request->customer);
        }
        if(isset($request->duration) && $request->duration != null){
            $start_date = explode(' to ',$request->duration)[0];
            $end_date = count(explode(' to ',$request->duration))==1?explode(' to ',$request->duration)[0]:explode(' to ',$request->duration)[1];
            $data->whereBetween('created_at', [ $start_date." 00:00:00", $end_date." 23:59:59"]);
        }

        $data = $data->orderBy('id','DESC')->get();
        $organizers = User::role('organization')->orderBy('id','DESC')->get();
        $customers = AppUser::orderBy('id','DESC')->get();

        return view('admin.report.admin_revenue_report', compact('data','request','organizers','customers'));
    }

    public function getStatistics($month){
        $day = Carbon::parse(Carbon::now()->year.'-'.Carbon::now()->month.'-01')->daysInMonth;
        if(Auth::user()->hasRole('admin')){
            $master['total_order'] = Order::whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
            $master['pending_order'] = Order::where('order_status','Pending')->whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
            $master['complete_order'] = Order::where('order_status','Complete')->whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
            $master['cancel_order'] = Order::where('order_status','Cancel')->whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
        }
        elseif(Auth::user()->hasRole('organization')){
            $master['total_order'] = Order::where('organization_id',Auth::user()->id)->whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
            $master['pending_order'] = Order::where([['order_status','Pending'],['organization_id',Auth::user()->id]])->whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
            $master['complete_order'] = Order::where([['order_status','Complete'],['organization_id',Auth::user()->id]])->whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
            $master['cancel_order'] = Order::where([['order_status','Cancel'],['organization_id',Auth::user()->id]])->whereBetween('created_at', [ Carbon::now()->year."-".$month."-01 00:00:00",  Carbon::now()->year."-".$month."-".$day." 23:59:59"])->count();
        }

        return response()->json(['success'=>true ,'data' =>$master ], 200);
    }

    public function settlementReport(){
        $data = User::role('organization')->orderBy('id','DESC')->get();
        $organizationIds = $data->pluck('id');
        $orderCounts = $this->groupedAggregate(
            Order::whereIn('organization_id', $organizationIds),
            'organization_id',
            'COUNT(*)'
        );
        $commissionExpression = 'SUM(org_commission + tax)';
        $totalCommission = $this->groupedAggregate(
            Order::where('payment_status', 1)->whereIn('organization_id', $organizationIds),
            'organization_id',
            $commissionExpression
        );
        $paidCommission = $this->groupedAggregate(
            Order::where([['payment_status', 1], ['org_pay_status', 1]])->whereIn('organization_id', $organizationIds),
            'organization_id',
            $commissionExpression
        );
        $unpaidCommission = $this->groupedAggregate(
            Order::where([['payment_status', 1], ['org_pay_status', 0]])->whereIn('organization_id', $organizationIds),
            'organization_id',
            $commissionExpression
        );

        foreach ($data as $value) {
            $value->total_orders = (int) ($orderCounts->get($value->id) ?? 0);
            $value->total_commission = (float) ($totalCommission->get($value->id) ?? 0);
            $value->pay_commission = (float) ($paidCommission->get($value->id) ?? 0);
            $value->organization_commission = (float) ($unpaidCommission->get($value->id) ?? 0);
        }

        return view('admin.report.admin_settlement_report', compact('data'));
    }

    public function viewSettlement($id){
        $data = Settlement::where('user_id',$id)->orderBy('id','DESC')->get();
        $this->attachSettlementUsers($data);

        return view('admin.report.view_settlement', compact('data'));
    }

    public function payToUser(Request $request){
        $data = $request->all();
        if($request->payment_type == "STRIPE"){
            $currency = Setting::find(1)->currency;
            $stripe_secret = PaymentSetting::find(1)->stripeSecretKey;
            Stripe\Stripe::setApiKey($stripe_secret);
            $stripeDetail =  Stripe\Charge::create ([
                "amount" => intval($request->payment) * 100,
                "currency" => $currency,
                "source" => $request->stripeToken,
            ]);
            $data['payment_token'] = $stripeDetail->id;
            $data['payment_status'] = 1;
        }
        Settlement::create($data);
        Order::where([['organization_id',$request->user_id],['payment_status',1],['org_pay_status',0]])->update(['org_pay_status'=>1]);
        return redirect()->back()->withStatus(__('Your Payment done successfully.'));
    }

    public function payToOrganization(Request $request){
        Settlement::create($request->all());
        Order::where([['organization_id',$request->user_id],['payment_status',1],['org_pay_status',0]])->update(['org_pay_status'=>1]);
        return response()->json(['msg' => null,'success'=>true], 200);
    }

    public function getQrCode($id)
    {
        $ticket = OrderChild::findOrFail($id);
        $order = Order::findOrFail($ticket->order_id);
        $this->authorizeOrderAccess($order);

        $ticket->order = Order::with(['customer:id,name,last_name', 'event:id,start_time,end_time,name,type,address', 'organization:id,image,first_name,last_name'])->find($ticket->order_id);
        $ticket->qrCode = QrCode::size(200)->generate($ticket->ticket_number);

        return view('admin.order.printTicket', compact('ticket'));
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'order_status' => 'required',
        ]);

        $order = Order::findOrFail($request->id);
        $this->authorizeOrderAccess($order);

        $order->update(['order_status' => $request->order_status]);

        return response()->json(['success' => true, 'msg' => 'Status Changed'], 200);
    }

    public function changePaymentStatus(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $order = Order::findOrFail($request->id);
        $this->authorizeOrderAccess($order);

        $order->update(['payment_status' => 1]);

        return response()->json(['success' => true, 'msg' => 'Status Changed'], 200);
    }

    public function orderInvoicePrint($order_id)
    {
        $order = Order::with(['customer', 'event', 'organization', 'ticket'])->findOrFail($order_id);
        $this->authorizeOrderAccess($order);

        $order->tax_data = OrderTax::with('tax')->where('order_id', $order->id)->get();
        $order->ticket_data = OrderChild::where('order_id', $order->id)->get();

        return view('admin.order.invoicePrint', compact('order'));
    }
}
