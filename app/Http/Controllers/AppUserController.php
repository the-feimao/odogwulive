<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\Order;
use Auth;
use DB;
use App\Models\User;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AppUserController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();

        if ($authUser->hasRole('admin')) {
            abort_if(Gate::denies('app_user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
            $users = AppUser::orderBy('id', 'DESC')->get();
        } elseif ($authUser->hasRole('organization')) {
            $users = AppUser::whereHas('orders', function ($query) use ($authUser) {
                $query->where('organization_id', $authUser->id);
            })->orderBy('id', 'DESC')->get();
        } else {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $readOnly = $authUser->hasRole('organization') && !$authUser->hasRole('admin');

        return view('admin.appUser.index', compact('users', 'readOnly'));
    }

    private function abortIfReadOnlyOrganizer(): void
    {
        $authUser = Auth::user();

        if ($authUser && $authUser->hasRole('organization') && !$authUser->hasRole('admin')) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }
    }

    public function create()
    {
        $this->abortIfReadOnlyOrganizer();
        abort_if(Gate::denies('app_user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.appUser.create');
    }

    public function store(Request $request)
    {
        $this->abortIfReadOnlyOrganizer();
        abort_if(Gate::denies('app_user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'name' => 'bail|required',
            'email' => 'bail|required|email|unique:app_user',
            'password' => 'bail|required|min:6',
        ]);
        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images/upload');
            $image->move($destinationPath, $name);
            $data['image'] = $name;
        } else {
            $data['image'] = 'defaultuser.png';
        }
        $data['provider'] = 'LOCAL';
        $data['language'] = Setting::first()->language;
        AppUser::create($data);
        return redirect()->route('app-user.index')->withStatus(__('User is added successfully.'));
    }

    public function show(AppUser $appUser)
    {
    }

    public function edit(AppUser $appUser)
    {
    }

    public function update(Request $request, AppUser $appUser)
    {
    }

    public function destroy(AppUser $appUser)
    {
    }

    public function blockUser($id)
    {
        $this->abortIfReadOnlyOrganizer();
        abort_if(Gate::denies('app_user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = AppUser::findOrFail($id);
        $user->status = $user->status == "1" ? "0" : "1";
        $user->save();
        return redirect()->route('app-user.index')->withStatus(__('User status changed successfully.'));
    }

    public function userDetail($id)
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin') && !$authUser->hasRole('organization')) {
            abort(403);
        }

        $user = AppUser::findOrFail($id);
        $following = array_filter(explode(',', (string) $user->following));
        $user->following = User::whereIn('id', $following)->get();

        if ($authUser->hasRole('admin')) {
            $user->pending = Order::where([['customer_id', $user->id], ['order_status', 'Pending']])->orderBy('id', 'DESC')->count();
            $user->complete = Order::where([['customer_id', $user->id], ['order_status', 'Complete']])->orderBy('id', 'DESC')->count();
            $user->cancel = Order::where([['customer_id', $user->id], ['order_status', 'Cancel']])->orderBy('id', 'DESC')->count();
            $user->upcoming = Order::with('event:id,name,start_time')->where([['customer_id', $user->id], ['order_status', 'Pending']])->orderBy('id', 'DESC')->get();
            $user->past = Order::with('event:id,name,start_time')->where([['customer_id', $user->id], ['order_status', 'Complete']])
                ->orWhere([['customer_id', $user->id], ['order_status', 'Cancel']])
                ->orderBy('id', 'DESC')->get();
        } elseif ($authUser->hasRole('organization')) {
            abort_unless(
                Order::where('customer_id', $user->id)->where('organization_id', $authUser->id)->exists(),
                Response::HTTP_FORBIDDEN,
                '403 Forbidden'
            );

            $user->pending = Order::where([['customer_id', $user->id], ['organization_id', $authUser->id], ['order_status', 'Pending']])->orderBy('id', 'DESC')->count();
            $user->complete = Order::where([['customer_id', $user->id], ['organization_id', $authUser->id], ['order_status', 'Complete']])->orderBy('id', 'DESC')->count();
            $user->cancel = Order::where([['customer_id', $user->id], ['organization_id', $authUser->id], ['order_status', 'Cancel']])->orderBy('id', 'DESC')->count();
            $user->upcoming = Order::with('event:id,name,start_time')->where([['customer_id', $user->id], ['organization_id', $authUser->id], ['order_status', 'Pending']])->orderBy('id', 'DESC')->get();
            $user->past = Order::with('event:id,name,start_time')->where(function ($query) use ($user, $authUser) {
                $query->where([['customer_id', $user->id], ['organization_id', $authUser->id], ['order_status', 'Complete']]);
            })->orWhere(function ($query) use ($user, $authUser) {
                $query->where([['customer_id', $user->id], ['organization_id', $authUser->id], ['order_status', 'Cancel']]);
            })->orderBy('id', 'DESC')->get();
        }

        return view('admin.appUser.view', compact('user'));
    }
}
