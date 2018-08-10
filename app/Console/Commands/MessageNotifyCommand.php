<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\BattleController;
use App\GM_modles\GMNoticeRewardsModel;
use App\GM_modles\GMNoticeEmailModel;
use App\UserModel;
use App\DefindMstModel;
use Log;
use DateTime;
class MessageNotifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {   $GMNoticeEmailModel=new GMNoticeEmailModel();
        $GMNoticeRewardsModel=new GMNoticeRewardsModel();
        $UserModel=new UserModel();
        $emailData=$GMNoticeEmailModel->where('status',0)->get();
        $DefindMstModel=new DefindMstModel();
        $definActive= $DefindMstModel->select('value1','value2')->where('defind_id',7)->first();
        foreach ($emailData as $key => $email) {
            if($email['user_group']==0){
                if($email['country']==1){
                $uList=$UserModel->select('u_id')
                        ->join('User_Character','User_Character.u_id','=','User.u_id')
                        ->where('User_Character.ch_lv','>=',$email['user_lv_from'])
                        ->where('User_Character.ch_lv','<=',$email['user_lv_to'])
                        ->Get();
                }
                else{
                   $uList=$UserModel->select('u_id')
                        ->join('User_Character','User_Character.u_id','=','User.u_id')
                        ->where('User_Character.ch_lv','>=',$email['user_lv_from'])
                        ->where('User_Character.ch_lv','<=',$email['user_lv_to'])
                        ->where('country','like','%'.$email['country'].'%')
                        ->Get(); 
                }
            }
            else if($email['user_group']==1){
                 if($email['country']==1){
                     $uList=$UserModel->select('u_id')
                        ->join('User_Character','User_Character.u_id','=','User.u_id')
                        ->where('User_Character.ch_lv','>=',$email['user_lv_from'])
                        ->where('User_Character.ch_lv','<=',$email['user_lv_to'])
                        ->where('u_payment','>',0)
                        ->Get();
                }
                else{
                     $uList=$UserModel->select('u_id')
                        ->join('User_Character','User_Character.u_id','=','User.u_id')
                        ->where('User_Character.ch_lv','>=',$email['user_lv_from'])
                        ->where('User_Character.ch_lv','<=',$email['user_lv_to'])
                        ->where('country','like','%'.$email['country'].'%')
                        ->Get(); 
                }

            }
            else($email['user_group']==2){
                 if($email['country']==1){

                    $activeLastTime=time()-$definActive['value1']*$definActive['value2'];
                    $uList=$UserModel->select('u_id')
                        ->join('User_Character','User_Character.u_id','=','User.u_id')
                        ->where('User_Character.ch_lv','>=',$email['user_lv_from'])
                        ->where('User_Character.ch_lv','<=',$email['user_lv_to'])
                        ->where('u_payment','>',0)
                        ->where('u_last_login','>=',$activeLastTime)
                        ->Get();
                    }
                    else{
                        $activeLastTime=time()-$definActive['value1']*$definActive['value2'];
                    $uList=$UserModel->select('u_id')
                        ->join('User_Character','User_Character.u_id','=','User.u_id')
                        ->where('User_Character.ch_lv','>=',$email['user_lv_from'])
                        ->where('User_Character.ch_lv','<=',$email['user_lv_to'])
                        ->where('u_payment','>',0)
                        ->where('u_last_login','>=',$activeLastTime)
                        ->where('country','like','%'.$email['country'].'%')
                        ->Get();
                    }
            }

            foreach ($uList as $key => $userData) {
                $emialReward=$GMNoticeRewardsModel->where('notice_id',$email['notice_id'])->get();
                $redisGm=Redis::connection('gm_user');
                $emailKey='email_pending'.$userData['u_id'];
                $redisGm->hset($emailKey,$emialReward);
            }
            
        }
        
    }

}
