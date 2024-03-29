<?php

namespace App\Http\Controllers;


use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRequest;
use App\Repositories\APIModelRepository;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userRepository;
    protected $centerRepository;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->userRepository = new APIModelRepository('App\User', '/api/users');
        $this->centerRepository = new APIModelRepository('App\Center', '/api/centers');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $users = $this->userRepository->findAll();

        return view('users.index', array('users' => $users));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('users.creation', array('centers' => $this->centerRepository->findAll()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(UserRequest $request)
    {
        $params = array_merge($request->all(), array('role' => 1));

        $params['password'] = password_hash($params['password'], PASSWORD_BCRYPT);

        $users = $this->userRepository->store($params);

        return redirect('/');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $user = new User();
        $user = $this->userRepository->find(array('id' => $id));

        return view('users.show', array('user' => $user));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $user = new User();
        $user = $this->userRepository->find(array('id' => $id));
        return view('users.edit', array('user' => $user, 'centers' => $this->centerRepository->findAll()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $params = array_merge($request->all(), array('id' => $id));
        unset($params['_token']);

        $this->userRepository->update($params);

        return redirect('/users/' . $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $this->userRepository->destroy(array('id' => $id));

        return redirect('/logout');
    }

    /**
     * Show log in form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function login()
    {
        return view('users.connection');
    }

    /**
     * Starts user session, according to user log in form
     *
     * @param UserLoginRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function connect(UserLoginRequest $request)
    {
        $params = array('email' => $request->input('email'));
        $user = $this->userRepository->find($params);

        if(password_verify($request->input('password'), $user->password))
        {
            session(['user'     => $user->id]);
            session(['username' => $user->name]);
            session(['role'     => $user->role->{'_id'}]);
            return redirect('/');
        }
        else
        {
            return redirect('/login')->withErrors(array('password' => 'Password and email don\'t match'));
        }
    }

    /**
     * Ends user session
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout(Request $request)
    {
        if($request->session()->has('user'))
            $request->session()->forget('user');

        return redirect('/');
    }
}
