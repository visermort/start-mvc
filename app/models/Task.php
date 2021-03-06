<?php
namespace app\models;

use app\Model;
use app\App;

/**
 * Class Task
 * @package app\models
 */
class Task extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';

    public static $rules = [
        'required'=> [['first_name', 'email', 'text', 'csrf']],
        'email' => [['email']],
        'regex' => [['email', '/^(?!(admin@)).*$/']]//to protect email like admin@someemail.com
    ];
    public static $rulesUpdate = [
        'required'=> [['text', 'csrf']],
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->belongsTo('app\models\User');
    }

    /**
     * make new task. Find user by email. Make new user if hi is not exists
     * @param $data
     * @return Task
     */
    public static function createNew($data)
    {
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            //create new user
            $user = new User();
            $user->email = strtolower($data['email']);
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->password = md5(time());//random password
            $user->save();
        }
        $task =  new self();
        $task->user_id = $user->id;
        $task->text = $data['text'];
        $task->save();
        return $task;
    }

    /**
     * events for model
     */
    public static function boot()
    {
        parent::boot();
        // on saved
        Task::saved(function ($task) {
            //after save clear cache
            App::getComponent('cache')->clear();
        });
    }

}