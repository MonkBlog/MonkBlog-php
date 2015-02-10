<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

    public static $rules = [
        'email' => 'required|email|unique:users,email,{id}',
        'first_name' => 'required',
        'last_name' => 'required',
        'display_name' => 'required|unique:users,display_name,{id}',
        'password' => 'required|confirmed',
    ];

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'owner',
        'display_name',
        'password'
    ];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [ 'password', 'password_confirmation', 'remember_token' ];

    public function update(Array $attributes = []) {
        //There's one user and they're not the owner of the site yet.
        //Or the owner is giving ownership to another user.
        if( array_key_exists( 'owner', $attributes ) ) {
            if( count( User::all() ) == 1 && $this->owner == 0 || $this->owner ) {
                $attributes[ 'owner' ] = true;
            }
            else {
                $attributes[ 'owner' ] = false;
            }
        }
        else if( $this->owner ) {
            $attributes[ 'owner' ] = true;
        }
        else {
            $attributes[ 'owner' ] = false;
        }

        parent::update($attributes);
    }

}
