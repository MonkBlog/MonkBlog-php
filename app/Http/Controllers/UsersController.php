<?php

use \Illuminate\Support\MessageBag;

class UsersController extends BaseController {

    /**
     * Display a listing of users
     *
     * @return Response
     */
    public function index() {
        $users = User::all();
        $pageTitle = 'Users';

        return View::make( 'users.index', compact( 'users', 'pageTitle' ) );
    }

    /**
     * Show the form for creating a new user
     *
     * @return Response
     */
    public function create() {
        $user = new User();
        $pageTitle = 'Create User';
        return View::make( 'users.create', compact( 'user' , 'pageTitle') );
    }

    /**
     * Store a newly created user in storage.
     *
     * @return Response
     */
    public function store()
    {
        $validator = Validator::make($data = Input::all(), User::$rules);

        if( $validator->fails() )
        {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        $data[ 'password' ] = Hash::make( $data[ 'password' ] );

        User::create( $data );

        return Redirect::route('admin.users.index');
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findOrFail( $id );
        $pageTitle = $user->display_name;

        return View::make( 'users.show', compact( 'user', 'pageTitle' ) );
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $pageTitle = 'Edit '. $user->display_name;

        $authUser = Auth::user();

        return View::make( 'users.edit', compact( 'user', 'authUser', 'pageTitle' ) );
    }

    /**
     * Update the specified user in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update( $id )
    {
        $user = User::findOrFail( $id );
        $data = Input::except( 'password', 'password_confirmation' );
        $passwordInput = Input::get( 'password' );

        $userRules = User::$rules;
        //allow a user to update their info
        $userRules[ 'email' ] = str_replace( '{id}', $user->id, $userRules[ 'email' ] );
        $userRules[ 'display_name' ] = str_replace( '{id}', $user->id, $userRules[ 'display_name' ] );
        $userRules[ 'password' ] = '';

        if( !Hash::check( $passwordInput, $user->getAuthPassword() ) ) {
            return Redirect::back()->withErrors(new MessageBag( [
                    'password' => 'Update failed, invalid password'
                ]) )->withInput()->exceptInput( 'password' );
        }

        $validator = Validator::make( $data, array_filter( $userRules ) );

        if( $validator->fails() ) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $user->update( $data );

        return Redirect::route('admin.users.index');
    }

    public function updatePassword( $id ) {
        /**
         * @todo add access for super admin once ACL is in place
         */
        if( Auth::id() != $id ) {
            return Redirect::route( 'admin.users.index' )->withErrors(
                new MessageBag( [
                    'change_password' => "You don't have the right permissions to change the selected Users password"
                ]) );
        }

        $user = User::find( $id );
        $pageTitle = 'Reset Password for '. $user->display_name;

        return View::make( 'users.edit-password', compact( 'user', 'pageTitle' ) );
    }

    public function savePassword( $id ) {
        /**
         * @todo add access for super admin once ACL is in place
         */
        if( Auth::id() != $id ) {
            return Redirect::route( 'admin.users.index' )->withErrors(
                new MessageBag( [
                    'change_password' => "You don't have the right permissions to change the selected Users password"
                ]) );
        }

        $user = User::find( $id );

        $data = Input::except( 'old_password' );

        if( !Hash::check( Input::get( 'old_password' ), $user->getAuthPassword() ) ) {
            return Redirect::back()->withErrors( new MessageBag( [
                'old_password' => 'Update failed, incorrect password supplied'
            ]) )->withInput()->exceptInput( 'password' );
        }

        $userRules = User::$rules;
        //allow a user to update their info
        $userRules[ 'email' ] = str_replace( '{id}', $user->id, $userRules[ 'email' ] );
        $userRules[ 'display_name' ] = str_replace( '{id}', $user->id, $userRules[ 'display_name' ] );

        $validator = Validator::make( $data, array_filter( $userRules ) );

        if( $validator->fails() ) {
            return Redirect::back()->withErrors($validator)->withInput()->exceptInput( 'password' );
        }

        $data[ 'password' ] = Hash::make( $data[ 'password' ] );

        $user->update( $data );

        return Redirect::route( 'admin.users.index' )->withInput( [ 'message' => 'Password successfully changed.' ] );

    }

    public function confirmDestroy( $id ) {
        $user = User::find( $id );

        $viewData = [
            'user' => $user,
            'pageTitle' => 'Confirm Delete ' . $user->display_name,
        ];

        return Response::view( 'users.destroy', $viewData );
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy( $id ) {
        User::destroy( $id );

        return Redirect::route( 'admin.users.index' );
    }

}
