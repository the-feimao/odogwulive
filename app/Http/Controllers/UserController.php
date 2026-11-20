<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\AppUser;
use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;
use Redirect;
use App;
use App\Models\Language;
use Rave;
use Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private function authorizeStaffPaymentOrder($id): Order
    {
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('admin')) {
            return $order;
        }

        if ($user->hasRole('organization') && (int) $order->organization_id === (int) $user->id) {
            return $order;
        }

        abort(403);
    }

    /**
     * Populate dashboard event aggregates with two grouped queries instead of
     * querying tickets and orders once per event.
     */
    private function applyEventAggregates($eventCollections): void
    {
        $events = collect($eventCollections)->flatMap(fn ($collection) => $collection);
        $eventIds = $events->pluck('id')->filter()->values();

        if ($eventIds->isEmpty()) {
            return;
        }

        $ticketTotals = Ticket::whereIn('event_id', $eventIds)
            ->select('event_id')
            ->selectRaw('SUM(quantity) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $soldTotals = Order::whereIn('event_id', $eventIds)
            ->select('event_id')
            ->selectRaw('SUM(quantity) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        foreach ($events as $event) {
            $event->tickets = (int) ($ticketTotals->get($event->id) ?? 0);
            $event->sold_ticket = (int) ($soldTotals->get($event->id) ?? 0);
            $event->average = $event->tickets == 0 ? 0 : $event->sold_ticket * 100 / $event->tickets;
            $event->avaliable = $event->tickets - $event->sold_ticket;
        }
    }
    public function index()
    {                    
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $users = User::with(['roles:id,name'])->get();
        return view('admin.user.index', compact('users')); 
    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $roles = Role::whereNotIn('name', ['admin'])->get();
        $orgs = User::role('organization')->orderBy('id','DESC')->get();  
        return view('admin.user.create', compact('roles','orgs'));
    }
   
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'bail|required',
            'last_name' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'phone' => 'bail|required',
            'password' => 'bail|required|min:6',
        ]);
        $data = $request->all();
        $data['password'] =  Hash::make($request->password); 
        $data['org_id'] = $request->organization;
        $data['language'] = Setting::first()->language;
        $user = User::create($data);
        $user->assignRole($request->input('roles', []));
    
        return redirect()->route('users.index')->withStatus(__('User is added successfully.'));
    }

    public function show(User $user)
    {
        return view('admin.user.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $roles = Role::all();
        $roles = Role::whereNotIn('name', ['admin'])->get();
        if($user->hasRole('admin')){
            return redirect()->route('users.index')->withStatus(__('You can not edit admin.'));
        }
        $orgs = User::role('organization')->orderBy('id','DESC')->get();  

        return view('admin.user.edit', compact('roles', 'user','orgs'));
    }
   
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'bail|required',
            'last_name' => 'bail|required',
            'phone' => 'bail|required',
            'email' => 'bail|required|unique:users,email,' . $user->id . ',id',
        ]);
        $data = $request->all();
        $data['org_id'] = $request->organization;
        $user->update($data);        
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')->withStatus(__('User is updated successfully.'));
    }

    public function destroy(User $user)
    {

    }

    public function adminDashboard(){  
        
        $master['organizations'] = User::role('organization')->count();
        $master['users'] = AppUser::count();
        $master['total_order'] = Order::count();
        $master['pending_order'] = Order::where('order_status','Pending')->count();
        $master['complete_order'] = Order::where('order_status','Complete')->count();
        $master['cancel_order'] = Order::where('order_status','Cancel')->count();
        $master['eventDate'] = array();
        $events = Event::where([['status',1],['is_deleted',0]])->orderBy('id','DESC')->get();              
        $day = Carbon::parse(Carbon::now()->year.'-'.Carbon::now()->month.'-01')->daysInMonth;                       
        $monthEvent = Event::whereBetween('start_time', [ date('Y')."-".date('m')."-01 00:00:00",  date('Y')."-".date('m')."-".$day." 23:59:59"])
        ->where([['status',1],['is_deleted',0]])
        ->orderBy('id','DESC')->get();
      
        $this->applyEventAggregates([$monthEvent, $events]);
        foreach ($events as $value) {
            array_push( $master['eventDate'],$value->start_time->format('Y-m-d'));
        }
    
        return view('admin.dashboard', compact('events','monthEvent','master'));
    }


    public function organizationDashboard(){        
        $master['total_tickets'] = Ticket::where('user_id',Auth::user()->id)->sum('quantity');
        $master['used_tickets'] = Order::where('organization_id',Auth::user()->id)->sum('quantity');
        $master['events'] = Event::where([['user_id',Auth::user()->id],['is_deleted',0]])->count();
        $master['total_order'] = Order::where('organization_id',Auth::user()->id)->count();
        $master['pending_order'] = Order::where([['order_status','Pending'],['organization_id',Auth::user()->id]])->count();
        $master['complete_order'] = Order::where([['order_status','Complete'],['organization_id',Auth::user()->id]])->count();
        $master['cancel_order'] = Order::where([['order_status','Cancel'],['organization_id',Auth::user()->id]])->count();
        $day = Carbon::parse(Carbon::now()->year.'-'.Carbon::now()->month.'-01')->daysInMonth; 
        $monthEvent = Event::whereBetween('start_time', [ date('Y')."-".date('m')."-01 00:00:00",  date('Y')."-".date('m')."-".$day." 23:59:59"])
        ->where([['status',1],['user_id',Auth::user()->id],['is_deleted',0]])
        ->orderBy('id','DESC')->get();      

        $events = Event::where([['status',1],['user_id',Auth::user()->id],['is_deleted',0]])->orderBy('id','DESC')->get(); 
        $this->applyEventAggregates([$monthEvent, $events]);

        $master['eventDate'] = array();
        foreach ($events as $value) {
            array_push( $master['eventDate'],$value->start_time->format('Y-m-d'));
        }
    
        return view('admin.org_dashboard', compact('events','monthEvent','master'));
    }

    public function viewProfile(){
        $languages = Language::where('status',1)->get();
        $followerIds = Auth::user()->hasRole('organization') ? Auth::user()->followers : [];
        return view('admin.profile', compact('languages', 'followerIds'));
    }

    public function editProfile(Request $request){
        User::find(Auth::user()->id)->update($request->all());
        if(session()->get('locale') != $request->language) {
            App::setLocale($request->language);
            session()->put('locale', $request->language);
            $direction = Language::where('name',$request->language)->first()->direction;
            session()->put('direction', $direction);
        }
        return redirect('profile')->withStatus(__('Profile is updated successfully.'));
    }

    public function changePassword(Request $request){
        $request->validate([
            'current_password' => 'bail|required',
            'password' => 'bail|required|min:6',
            'confirm_password' => 'bail|required|same:password|min:6'
        ]);

        if (Hash::check($request->current_password, Auth::user()->password)){
            User::find(Auth::user()->id)->update(['password'=>Hash::make($request->password)]);
            return redirect('profile')->withStatus(__('Password is updated successfully.'));          
        }
        else{
            return Redirect::back()->with('error_msg','Current Password is wrong!');
        }
    }

    public function makePayment($id)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $this->authorizeStaffPaymentOrder($id);
        $order = Order::with(['customer'])->find($id);
        return view('createPayment', compact('order'));
    }

    public function transction_verify(Request $request, $order_id)
    {
        $order = Order::find($order_id);
        if (!$order) {
            return view('cancel');
        }

        $transactionId = $request->input('transaction_id');
        $status = strtolower((string) $request->input('status'));

        // Record the gateway reference but do not mark the order as paid based on
        // unverified client/browser callback parameters.
        if (!empty($transactionId)) {
            $order->payment_token = $transactionId;
            $order->save();
        }

        if ($status === 'successful') {
            return view('transction_verify', [
                'title' => __('Payment Received'),
                'message' => __('Your payment reference was received and is pending verification.'),
            ]);
        }

        return view('cancel');
    }

    public function initialize(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $this->authorizeStaffPaymentOrder($id);
        Rave::initialize(route('callback', $id));
    }

    public function callback(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return view('cancel');
        }

        $paymentToken = null;
        $resp = $request->input('resp');
        if (is_string($resp)) {
            $decoded = json_decode($resp);
            $paymentToken = data_get($decoded, 'tx.paymentId') ?? data_get($decoded, 'data.id');
        }

        if (!empty($paymentToken)) {
            $order->payment_token = $paymentToken;
            $order->save();
        }

        // Do not mark the order as paid without server-side gateway verification.
        return view('transction_verify', [
            'title' => __('Payment Received'),
            'message' => __('Your payment reference was received and is pending verification.'),
        ]);
    }

    public function changeLanguage($lang)
    {
        App::setLocale($lang);
        session()->put('locale', $lang);
        $dir = Language::where('name',$lang)->first()->direction;
        session()->put('direction', $dir);
        return redirect()->back();
    }

    public function scanner(){
        if(Auth::user()->hasRole('admin'))
        {
            $scanners = User::role('scanner')->orderBy('id','DESC')->get();
        }
        else{
            $scanners = User::role('scanner')->where('org_id',Auth::user()->id)->orderBy('id','DESC')->get();
        }   
        foreach ($scanners as $value) {
            $value->total_event = Event::where('scanner_id',$value->id)->count();
        }             
        return view('admin.scanner.index',compact('scanners'));
    }
    
    public function scannerCreate(){
        return view('admin.scanner.create');
    }

    public function addScanner(Request $request){
      
        $request->validate([
            'first_name' => 'bail|required',
            'last_name' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'phone' => 'bail|required',
            'password' => 'bail|required|min:6',
        ]);
        $data = $request->all();
        $data['org_id'] = Auth::user()->id;
        $data['password'] =  Hash::make($request->password); 
       
        $data['language'] = Setting::first()->language;
        $user = User::create($data);           
        $user->assignRole('scanner');
        return redirect('scanner')->withStatus(__('Scanner is added successfully.'));
    }

    public function blockScanner($id){
        $user = User::find($id);  
        $user->status = $user->status == "1" ? "0" : "1";
        $user->save();        
        return redirect('scanner')->withStatus(__('User status changed successfully.'));
    }

    public function getScanner($id){
        $data = User::where('org_id',$id)->orderBy('id','DESC')->get();
        return response()->json(['data' => $data,'success'=>true], 200); 
    }
}
