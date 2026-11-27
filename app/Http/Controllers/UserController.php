<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\DashboardMetrics;
use App\Support\PaystackTransferClient;
use App\Support\PaystackTransferException;
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
use Illuminate\Support\Facades\Cache;
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
     * The admin role picker submits role IDs as strings. Spatie treats string
     * values as role names, so cast them to integers before assignment.
     */
    private function roleIdsFromRequest(Request $request): array
    {
        return collect($request->input('roles', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
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
            ->where([
                ['is_deleted', 0],
                ['status', 1],
            ])
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
            $event->capacity_exceeded = $event->tickets > (int) ($event->people ?? 0);
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
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);
        $data = $request->all();
        $data['password'] =  Hash::make($request->password); 
        $data['org_id'] = $request->organization;
        $data['language'] = Setting::first()->language;
        $user = User::create($data);
        $user->assignRole($this->roleIdsFromRequest($request));
    
        return redirect()->route('users.index')->withStatus(__('User is added successfully.'));
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.user.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $roles = Role::whereNotIn('name', ['admin'])->get();
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')->withStatus(__('You can not edit admin.'));
        }
        $orgs = User::role('organization')->orderBy('id', 'DESC')->get();

        return view('admin.user.edit', compact('roles', 'user', 'orgs'));
    }

    public function update(Request $request, User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')->withStatus(__('You can not edit admin.'));
        }

        $request->validate([
            'first_name' => 'bail|required',
            'last_name' => 'bail|required',
            'phone' => 'bail|required',
            'email' => 'bail|required|unique:users,email,' . $user->id . ',id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);
        $data = $request->all();
        $data['org_id'] = $request->organization;
        if (empty($request->password)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        $user->syncRoles($this->roleIdsFromRequest($request));

        return redirect()->route('users.index')->withStatus(__('User is updated successfully.'));
    }

    public function destroy(User $user)
    {

    }

    public function adminDashboard()
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $master['organizations'] = User::role('organization')->count();
        $master['users'] = AppUser::count();
        $orderCounts = Order::query()
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');
        $master['total_order'] = (int) $orderCounts->sum();
        $master['pending_order'] = (int) ($orderCounts->get('Pending') ?? 0);
        $master['complete_order'] = (int) ($orderCounts->get('Complete') ?? 0);
        $master['cancel_order'] = (int) ($orderCounts->get('Cancel') ?? 0);
        $master['eventDate'] = array();
        $events = Event::where([['status', 1], ['is_deleted', 0]])->orderBy('id', 'DESC')->get();
        $day = Carbon::parse(Carbon::now()->year . '-' . Carbon::now()->month . '-01')->daysInMonth;
        $monthEvent = Event::whereBetween('start_time', [date('Y') . "-" . date('m') . "-01 00:00:00",  date('Y') . "-" . date('m') . "-" . $day . " 23:59:59"])
            ->where([['status', 1], ['is_deleted', 0]])
            ->orderBy('id', 'DESC')->get();

        $this->applyEventAggregates([$monthEvent, $events]);
        foreach ($events as $value) {
            array_push($master['eventDate'], $value->start_time->format('Y-m-d'));
        }

        $metrics = DashboardMetrics::forScope();
        $series = DashboardMetrics::monthly();

        return view('admin.dashboard', compact('events', 'monthEvent', 'master', 'metrics', 'series'));
    }

    public function organizationDashboard()
    {
        if (!Auth::user()->hasRole('organization')) {
            abort(403);
        }

        $master['total_tickets'] = Ticket::where('user_id', Auth::user()->id)
            ->where([
                ['is_deleted', 0],
                ['status', 1],
            ])
            ->sum('quantity');
        $master['used_tickets'] = Order::where('organization_id', Auth::user()->id)->sum('quantity');
        $master['events'] = Event::where([['user_id', Auth::user()->id], ['is_deleted', 0]])->count();
        $orderCounts = Order::where('organization_id', Auth::user()->id)
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');
        $master['total_order'] = (int) $orderCounts->sum();
        $master['pending_order'] = (int) ($orderCounts->get('Pending') ?? 0);
        $master['complete_order'] = (int) ($orderCounts->get('Complete') ?? 0);
        $master['cancel_order'] = (int) ($orderCounts->get('Cancel') ?? 0);
        $day = Carbon::parse(Carbon::now()->year . '-' . Carbon::now()->month . '-01')->daysInMonth;
        $monthEvent = Event::whereBetween('start_time', [date('Y') . "-" . date('m') . "-01 00:00:00",  date('Y') . "-" . date('m') . "-" . $day . " 23:59:59"])
            ->where([['status', 1], ['user_id', Auth::user()->id], ['is_deleted', 0]])
            ->orderBy('id', 'DESC')->get();

        $events = Event::where([['status', 1], ['user_id', Auth::user()->id], ['is_deleted', 0]])->orderBy('id', 'DESC')->get();
        $this->applyEventAggregates([$monthEvent, $events]);

        $master['eventDate'] = array();
        foreach ($events as $value) {
            array_push($master['eventDate'], $value->start_time->format('Y-m-d'));
        }

        $metrics = DashboardMetrics::forScope((int) Auth::user()->id);
        $series = DashboardMetrics::monthly((int) Auth::user()->id);
        $payoutReady = Auth::user()->hasPayoutDetails();

        return view('admin.org_dashboard', compact(
            'events',
            'monthEvent',
            'master',
            'metrics',
            'series',
            'payoutReady'
        ));
    }

    public function viewProfile(){
        $languages = Language::where('status',1)->get();
        $followerIds = Auth::user()->hasRole('organization') ? Auth::user()->followers : [];
        return view('admin.profile', compact('languages', 'followerIds'));
    }

    public function editProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->only(['name', 'first_name', 'last_name', 'phone', 'bio', 'country', 'language']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images/upload');
            $image->move($destinationPath, $name);
            $data['image'] = $name;
        }

        $user->update($data);

        if ($request->filled('language') && session()->get('locale') != $request->language) {
            $language = Language::where('name', $request->language)->first();
            if ($language) {
                App::setLocale($request->language);
                session()->put('locale', $request->language);
                session()->put('direction', $language->direction);
            }
        }
        return redirect('profile')->withStatus(__('Profile is updated successfully.'));
    }

    /**
     * Bank list for the organizer payout form. Fetching happens here rather
     * than during page render so opening the profile never makes an external
     * API call.
     */
    public function paystackBanks()
    {
        if (!Auth::check() || !Auth::user()->hasRole('organization')) {
            abort(403);
        }

        try {
            $banks = Cache::remember('paystack.banks.nigeria', now()->addDay(), function () {
                return app(PaystackTransferClient::class)->banks();
            });
        } catch (PaystackTransferException $exception) {
            report($exception);
            $banks = [];
        }

        return response()->json(['banks' => $banks], 200);
    }

    public function savePayoutAccount(Request $request)
    {
        if (!Auth::check() || !Auth::user()->hasRole('organization')) {
            abort(403);
        }

        $user = Auth::user();
        $existingNumber = (string) ($user->account_number ?? '');

        $validated = $request->validate([
            'bank_code' => 'bail|required|string|max:10',
            'bank_name' => 'bail|required|string|max:120',
            'account_number' => 'bail|nullable|string|max:20|regex:/^\d{10}$/',
            'account_name' => 'bail|required|string|max:120',
            'verify' => 'nullable|boolean',
        ]);

        if (blank($validated['account_number'] ?? null) && $existingNumber === '') {
            return redirect()->back()->withErrors([
                'account_number' => __('Enter an account number.'),
            ]);
        }

        $accountNumber = filled($validated['account_number'] ?? null)
            ? (string) $validated['account_number']
            : $existingNumber;

        $detailsChanged = (string) $user->bank_code !== (string) $validated['bank_code']
            || $accountNumber !== $existingNumber;

        $data = [
            'bank_code' => $validated['bank_code'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $accountNumber,
            'account_name' => $validated['account_name'],
            'payout_currency' => DashboardMetrics::currencyCode(),
        ];

        if ($request->boolean('verify')) {
            try {
                $resolved = app(PaystackTransferClient::class)
                    ->resolveAccount($accountNumber, (string) $validated['bank_code']);
                $data['account_name'] = (string) ($resolved['account_name'] ?? $data['account_name']);
                $data['payout_verified_at'] = now();
                $detailsChanged = true;
            } catch (PaystackTransferException $exception) {
                report($exception);

                return redirect()->back()->with('error', __('Paystack could not verify that account. Check the bank and account number.'));
            }
        } elseif (! $detailsChanged) {
            $data['payout_verified_at'] = $user->payout_verified_at;
        } else {
            $data['payout_verified_at'] = null;
        }

        if ($detailsChanged) {
            $data['paystack_recipient_code'] = null;
        }

        $user->update($data);

        return redirect('profile')->withStatus(__('Payout details are saved successfully.'));
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

    public function scanner()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $scanners = User::role('scanner')->orderBy('id', 'DESC')->get();
        } elseif ($user->hasRole('organization')) {
            $scanners = User::role('scanner')->where('org_id', $user->id)->orderBy('id', 'DESC')->get();
        } else {
            abort(403);
        }
        $events = Event::query()
            ->where('events.is_deleted', 0)
            ->when($user->hasRole('organization') && !$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('events.user_id', $user->id);
            })
            ->select('events.id', 'events.name', 'events.start_time', 'events.scanner_id')
            ->selectRaw('COUNT(order_child.id) as tickets_sold')
            ->selectRaw('SUM(CASE WHEN order_child.status = 1 THEN 1 ELSE 0 END) as checked_in')
            ->selectRaw('SUM(CASE WHEN order_child.status = 0 THEN 1 ELSE 0 END) as remaining')
            ->leftJoin('orders', 'orders.event_id', '=', 'events.id')
            ->leftJoin('order_child', 'order_child.order_id', '=', 'orders.id')
            ->groupBy('events.id', 'events.name', 'events.start_time', 'events.scanner_id')
            ->orderBy('events.id', 'DESC')
            ->get();

        $events->each(function ($event) {
            $event->tickets_sold = (int) $event->tickets_sold;
            $event->checked_in = (int) $event->checked_in;
            $event->remaining = (int) $event->remaining;
        });

        // Load assignments in one query. The ticket counts above never join the
        // pivot table, so sharing an event cannot duplicate ticket rows.
        $pivotNames = DB::table('event_scanner')
            ->join('users', 'users.id', '=', 'event_scanner.scanner_id')
            ->whereIn('event_scanner.event_id', $events->pluck('id')->all())
            ->select('event_scanner.event_id', 'event_scanner.scanner_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', users.first_name, users.last_name)), ''), NULLIF(users.name, ''), users.email) as scanner_name")
            ->get()
            ->groupBy('event_id')
            ->map(fn ($rows) => $rows->mapWithKeys(fn ($row) => [(int) $row->scanner_id => $row->scanner_name]));

        $scannersById = $scanners->keyBy('id');
        $scannerStats = collect();

        $events->each(function ($event) use ($pivotNames, $scannersById, $scannerStats) {
            $names = $pivotNames->get($event->id, collect());
            $ids = $event->scanner_id ? [(int) $event->scanner_id] : [];

            foreach ($names->keys() as $id) {
                if (! in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }

            $event->assigned_scanners = collect($ids)->map(function ($id) use ($names, $scannersById) {
                $name = $names->get($id);

                if (! $name) {
                    $scanner = $scannersById->get($id);
                    $name = $scanner
                        ? (trim($scanner->first_name.' '.$scanner->last_name) ?: ($scanner->name ?: $scanner->email))
                        : __('Scanner unavailable');
                }

                return (object) ['id' => $id, 'name' => $name];
            })->values();

            $event->assigned_scanner_name = $event->assigned_scanners->first()->name ?? null;
            $event->scanner_names = $event->assigned_scanners->pluck('name')->filter()->implode(', ');

            foreach ($event->assigned_scanners as $assignment) {
                $stat = $scannerStats->get($assignment->id) ?: (object) [
                    'total_event' => 0,
                    'checked_in' => 0,
                    'remaining' => 0,
                ];
                $stat->total_event += 1;
                $stat->checked_in += $event->checked_in;
                $stat->remaining += $event->remaining;
                $scannerStats->put($assignment->id, $stat);
            }
        });

        foreach ($scanners as $value) {
            $stat = $scannerStats->get($value->id);
            $value->total_event = (int) ($stat->total_event ?? 0);
            $value->checked_in = (int) ($stat->checked_in ?? 0);
            $value->remaining = (int) ($stat->remaining ?? 0);
        }
        return view('admin.scanner.index', compact('scanners', 'events'));
    }

    public function scannerCreate()
    {
        if (!Auth::user()->hasRole('organization') && !Auth::user()->hasRole('admin')) {
            abort(403);
        }
        return view('admin.scanner.create');
    }

    public function addScanner(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('organization') && !$user->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'first_name' => 'bail|required',
            'last_name' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'phone' => 'bail|required',
            'password' => 'bail|required|min:6',
        ]);
        $data = $request->all();
        $data['org_id'] = $user->hasRole('organization') ? $user->id : $request->org_id;
        $data['password'] = Hash::make($request->password);

        $data['language'] = Setting::first()->language;
        $scanner = User::create($data);
        $scanner->assignRole('scanner');
        return redirect('scanner')->withStatus(__('Scanner is added successfully.'));
    }

    public function blockScanner($id)
    {
        $authUser = Auth::user();
        $user = User::role('scanner')->findOrFail($id);

        if (!$authUser->hasRole('admin') && !($authUser->hasRole('organization') && (int) $user->org_id === (int) $authUser->id)) {
            abort(403);
        }

        $user->status = $user->status == "1" ? "0" : "1";
        $user->save();
        return redirect('scanner')->withStatus(__('User status changed successfully.'));
    }

    public function getScanner($id)
    {
        $authUser = Auth::user();
        if ($authUser->hasRole('organization') && (int) $id !== (int) $authUser->id) {
            abort(403);
        }
        if (!$authUser->hasRole('admin') && !$authUser->hasRole('organization')) {
            abort(403);
        }

        $data = User::where('org_id', $id)->role('scanner')->orderBy('id', 'DESC')->get();
        return response()->json(['data' => $data, 'success' => true], 200);
    }
}
