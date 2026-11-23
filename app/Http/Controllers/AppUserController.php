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
        abort_if(Gate::denies('app_user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $users = AppUser::OrderBy('id', 'DESC')->get();
        return view('admin.appUser.index', compact('users'));
    }

    public function create()
    {
        abort_if(Gate::denies('app_user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.appUser.create');
    }

    public function store(Request $request)
    {
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
