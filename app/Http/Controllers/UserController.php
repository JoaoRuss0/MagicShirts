<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UserUpdateRequest;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index')
            ->with('title',"Users")
            ->with('users', User::orderBy('tipo')
                ->orderBy('bloqueado')
                ->orderBy('name')
                ->paginate(20)->onEachSide(2));
    }

    public function show(User $user)
    {
        return view('users.show')
            ->with('title',"Users")
            ->with('user', $user);
    }

    public function filter(Request $request)
    {
        $user_querry = User::query();
        $last_filter = [];
        $NO_PARAMETERS = TRUE;

        if(!empty($request->name))
        {
            $user_querry = $user_querry->where('name', 'LIKE', "%$request->name%");
            $last_filter['name'] = $request['name'];
            $NO_PARAMETERS = FALSE;
        }

        if(!empty($request->tipo))
        {
            $user_querry = $user_querry->where('tipo', "$request->tipo");
            $last_filter['tipo'] = $request['tipo'];
            $NO_PARAMETERS = FALSE;
        }

        if(!empty($request->bloqueado))
        {
            $user_querry = $user_querry->where('bloqueado', ($request->bloqueado == "Yes") ? 1 : 0);
            $last_filter['bloqueado'] = $request['bloqueado'];
            $NO_PARAMETERS = FALSE;
        }

        if(!$NO_PARAMETERS)
        {
            $query_result = $user_querry
                ->orderBy('tipo')
                ->orderBy('bloqueado')
                ->orderBy('name')
                ->paginate(20)
                ->onEachSide(2)
                ->withQueryString();

            $last_filter['querry_count'] = $query_result->count();

            return view('users.index')
            ->with('title', "Users")
            ->with('last_filter', $last_filter)
            ->with('users', $query_result);
        }

        return view('users.index')
            ->with('title', "Users")
            ->with('last_filter', $last_filter)
            ->with('users', User::orderBy('tipo')
                ->orderBy('bloqueado')
                ->orderBy('name')
                ->paginate(20)
                ->onEachSide(2));
    }

    public function create()
    {
        return view('users.create')
            ->with('title',"Create User");
    }

    public function store(UserStoreRequest $request)
    {
        try
        {
            $validated = $request->validated();
            $user = new User();
            $user->fill($validated);

            if($request->hasFile('photo') != null)
            {
                $photo_path = $request->file('photo')->store("public/fotos");
                $user->foto_url= basename($photo_path);

                //$old_name = explode("/", $photo_path);
                //$new_name =  $old_name[0] . "/" . $old_name[1] . "/" . $user->id . "_" . $old_name[2];
                //Storage::move($photo_path, $new_name);
                //$user->foto_url= $user->id . "_" . $old_name[2];
            }

            $user->password = Hash::make($user->password);
            $user->save();

            return redirect()->route('users.index')
                ->with('message', "User account successfully created!")
                ->with('message_type', "message_success");

        }
        catch(Exception $e)
        {
            // WithInput() is used in case request goes through validators without a problem but fails to create user
            return back()->withInput()
                ->with('message', "Error creating user account.")
                ->with('message_type', "message_error");
        }
    }

    public function edit(User $user)
    {
        return view('users.edit')
            ->with('title', "Edit User")
            ->with('user', $user);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        // Admin can't alter his own type or block himself
        if(Auth::user()->id == $user->id && ($request->bloqueado != $user->bloqueado || $request->tipo != $user->tipo))
        {
            return back()
                ->with('message', "Error updating user account.")
                ->with('message_type', "message_error");
        }

        try
        {
            $validated = $request->validated();

            if($request->filled('password'))
            {
                $validated['password'] = Hash::make($request->password);
            }
            else
            {
                $validated['password'] = $user->password;
            }

            $user->fill($validated);

            if($request->hasFile('photo') != null)
            {
                Storage::delete("public/fotos/" . $user->foto_url);
                $photo_path = $request->file('photo')->store("public/fotos");
                $user->foto_url= basename($photo_path);
            }

            $user->update();

            return back()
                ->with('message', "User account successfully updated!")
                ->with('message_type', "message_success");

        }
        catch(Exception $e)
        {
            // WithInput() is used in case request goes through validators without a problem but fails to create user
            return back()->withInput()
                ->with('message', "Error updating user account.")
                ->with('message_type', "message_error");
        }
    }

    public function alterBlocked(User $user)
    {
        try
        {
            $user->update(['bloqueado' => !$user->bloqueado]);

            return back()
                ->with('message', "User #$user->id successfully " . ($user->bloqueado ? "blocked" : "unblocked") . "!")
                ->with('message_type', "message_success");
        }
        catch(Exception $e)
        {
            return back()
                ->with('message', "Failed to " . ($user->bloqueado ? "blocked" : "unblocked") . " user #$user->id.")
                ->with('message_type', "message_error");
        }
    }

    public function destroy(User $user)
    {
        try
        {
            $old_id = $user->id;
            $user->delete();

            return back()
                ->with('message', "User #$old_id successfully deleted!")
                ->with('message_type', "message_success");
        }
        catch (Exception $e)
        {
            return back()
                ->with('message', "Failed to delete user #$old_id.")
                ->with('message_type', "message_error");
        }
    }
}
