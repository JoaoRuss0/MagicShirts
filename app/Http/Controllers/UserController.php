<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index')
            ->with("title","Users")
            ->with("users", User::all()
                ->sortBy('bloqueado')
                ->sortBy('tipo')
                ->sortBy('name'));
    }

    public function create()
    {
        return view('users.create')->with("title","Create User");
    }

    public function store(UserStoreRequest $request)
    {
        try
        {
            $user = new User();
            $user->fill($request->validated());

            DB::beginTransaction();

            $user->save();

            if($request->hasFile('photo') != null)
            {
                $photo_path = $request->file('photo')->store('public/fotos');
                $old_name = explode("/", $photo_path);
                $new_name =  $old_name[0] . "/" . $old_name[1] . "/" . $user->id . "_" . $old_name[2];
                Storage::move($photo_path, $new_name);
                $user->foto_url= $user->id . "_" . $old_name[2];
            }

            $user->update();

            DB::commit();

            return redirect()->route('login')
                ->with('message', "Client account successfully created!")
                ->with('message_type', "message_success");

        }
        catch(Exception $e)
        {
            DB::rollback();

            return back()->withInput()
                ->with('message', "Error creating client account.")
                ->with('message_type', "message_error");
        }
    }

    public function filter(Request $request)
    {
        $user_querry = User::query();
        $last_filter = [];

        if(!empty($request->name))
        {
            $user_querry = $user_querry->where('name', 'LIKE', "%$request->name%");
            $last_filter['name'] = $request['name'];
        }

        if(!empty($request->tipo))
        {
            $user_querry = $user_querry->where('tipo', "$request->tipo");
            $last_filter['tipo'] = $request['tipo'];
        }

        if(!empty($request->bloqueado))
        {
            $user_querry = $user_querry->where('bloqueado', ($request->bloqueado == "Yes") ? 1 : 0);
            $last_filter['bloqueado'] = $request['bloqueado'];
        }

        return view('users.index')
            ->with('title', 'Users')
            ->with('last_filter', $last_filter)
            ->with('users', $user_querry->get()
                ->sortBy('bloqueado')
                ->sortBy('tipo')
                ->sortBy('name'));
    }
}
