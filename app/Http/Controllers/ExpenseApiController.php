<?php

namespace App\Http\Controllers;
use App\Models\expense;
use App\Models\expense_post;
use Illuminate\Http\Request;

class ExpenseApiController extends Controller
{
    //
    function index($id){
       
        $expenses=new expense;
        $expenseAmount=$expenses->where('from_user_id',$id)->sum('amount');
        $expenseDetails=$expenses->where('from_user_id',$id)
        ->join('users As u1','u1.id','=','expenses.from_user_id')
        ->join('users As u2','u2.id','=','expenses.to_user_id')
        ->get([ 'u1.name as usr1','u2.name as usr2','expenses.amount']);
       

        $returnAmount=$expenses->where('to_user_id',$id)->sum('amount');
        $returnAmountDetails=$expenses->where('to_user_id',$id)
        ->join('users As u1','u1.id','=','expenses.from_user_id')
        ->join('users As u2','u2.id','=','expenses.to_user_id')
        ->get([ 'u1.name as usr1','u2.name as usr2','expenses.amount']);
        return ['expenseAmount'=>$expenseAmount,'expenseAmountDeatils'=>$expenseDetails,'balanceAmount'=> $returnAmount-$expenseAmount,'balanceDetails'=>$returnAmountDetails];
    }
    function store(Request $req){
        $this->validate($req,[
            'user_id' => 'required',
            'amount' => 'required',
            'number_users'=>'required',
            'users'=>'required',
            'split_type'=>'required'
        ]);
        
        $users=array_map('intval', explode(',', $req->users));
        $users[]=$req->user_id;
        $users= array_unique($users);
       
        if(in_array($req->split_type,['EXACT' ,'PERCENT'])){
            $this->validate($req,[
                'split_values'=>'required'   
            ]);
            $balance=0;
            $splitAmt=explode(',',trim($req->split_values,','));
            if($req->split_type=='EXACT'){
                if(array_sum($splitAmt)!=$req->amount){
                    return response()->json([
                        'message' => 'Invalid Amount'
                    ], 422 );  
                }
                $userData= array_map(function(int $value,$amount): array {
                    return array('id'=>$value,'amount'=>$amount);
                }, explode(',', $req->users),$splitAmt);
            }else{
                
                if(array_sum($splitAmt)!=100){
                    return response()->json([
                        'message' => 'Invalid Percentage'
                    ], 422 );  
                }
                $userData= array_map(function(int $value,$perc,$amount): array {
                    return array('id'=>$value,'amount'=>($perc*$amount)/100);
                },  
                $users,
                $splitAmt,
                array_fill(0,count($users), $req->amount)
                );
            }

        }else{
            if(count($users)<1){
                return response()->json([
                    'message' => 'Invalid users'
                ], 422 );
            }else{
                $equalAmt=($req->amount)/count($users);
                $equalAmt=number_format((float)$equalAmt, 2, '.', ''); 
                $balance=$req->amount-($equalAmt*count($users));
                $userData = array_map(function(int $value,$amount): array {
                    return array('id'=>$value,'amount'=>$amount);
                }, $users,array_fill(0,count($users), $equalAmt));
            }
             
        }
        
        if(count($userData)>0){
            $expense_post=new expense_post;
            $expense_post->user_id=$req->user_id;
            $expense_post->amount=$req->amount;
            $expense_post->number_users=$req->number_users;
            $expense_post->split_type=$req->split_type;
            $expense_post->split_users=json_encode(explode(',', $req->users));
            $expense_post->split_values=json_encode(explode(',',trim($req->split_values,',')));
            $expense_post->save();
            //insert expense
            
            $insert=[];
            foreach($userData as $key=>$userVal){

                if($req->user_id!=$userVal['id']){
                    $expense= new expense;
                    $amount=($key==0)?($userVal['amount']+$balance):$userVal['amount'];
                    $expUserAmountTot=0;
                   
                    $expUser=$expense->where(["from_user_id"=> $req->user_id,"to_user_id"=>$userVal['id'],['amount','>',0]]);
                        if($expUser->count()>0){
                            $expUserData=$expUser->first();
                            $expUserAmountTot=$expUserData->amount;
                            $expUserAmount=max(($expUserData->amount-$amount), 0);
                            $update=[
                                'amount'=>$expUserAmount,
                            ];
                            $expUser->update($update);
                        }else{
                            $expUserAmount= $amount;
                        }

                    $exp=$expense->where(["from_user_id"=> $userVal['id'],"to_user_id"=>$req->user_id]);
                    if($exp->count()>0){
                        $exp_data=$exp->first();
                        
                        $insert=[
                            'amount'=>$exp_data->amount+$expUserAmount,
                        ];
                        $exp->update($insert);
                    }else{
                        $insert=[
                            'user_id'=>$req->user_id,
                            'amount'=>max($amount-$expUserAmountTot,0),
                            'from_user_id'=>$userVal['id'],
                            'to_user_id'=>$req->user_id,
                        
                        ];
                    $expense->insert($insert);
                    }
                 }
            }
            if($insert)
                return response()->json([
                    'status'=>'success',
                    'message' => 'Expenses shared successfully '
                ], 200 );
            }   
      
    }
}
