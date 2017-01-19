<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\{Input,Validator};

use App\Model\{Users,Storage};
use App\Dao\{LoginDao,AjaxDao};

use Lib\PublicClass\{S};

class IndexController extends Controller
{
    public function login()
    {
        $user = session('user');
        if( !LoginDao::verify( $user ))
        {
            return view('index.login');
        }
        else
        {
            return redirect('home');
        }
    }
    public function logout()
    {
        session()-> flush();
        return redirect('login');
    }

    public function loginVerify()
    {
        $post  = Input::all();

        $validator = Validator::make( $post, [
            'account'    => 'required',
            'password'   => 'required',
            'verify_code'=> 'required',
        ], [
            'account.required'    => '用户名必须写',
            'password.required'   => '密码必须写',
            'verify_code.required'=> '验证码必须写',
        ]);
        if( $validator-> fails() )
        {
            return view('index.login')-> withErrors( $validator );
        }

        if( $post['verify_code'] != session( 'phrase' ))
        {
            $validator-> errors()-> add('error', '验证码错误');
            return view('index.login')-> withErrors( $validator );
        }

        $users = Users::join( 'role_user as b', 'users.id', '=', 'b.user_id' )
            -> join( 'roles as c', 'b.role_id', '=', 'c.id' )
            -> join( 'storage as d', 'users.storage_id', '=', 'd.id' )
            -> select( 'users.*', 'b.role_id', 'c.name as role_name', 'c.is_admin as role_admin', 'd.acronym', 'd.name as storage_name',
                'd.parent_id as storage_parent_id', 'd.level', 'd.status as storage_status' )
            -> orWhere( 'account', $post["account"] )
            -> orWhere( 'email', $post["account"] )
            -> get();

        if( $users-> count() > 1 )
        {
            $validator-> errors()-> add('error', '存在重复用户');
            return view('index.login')-> withErrors( $validator );
        }
        if( $users-> count() < 1 )
        {
            $validator-> errors()-> add('error', '用户不存在');
            return view('index.login')-> withErrors( $validator );
        }

        $user = $users[0]-> toArray();

        if( $user['password'] != md5( $post['password'] ))
        {
            $validator-> errors()-> add('error', '密码错误');
            return view('index.login')-> withErrors( $validator );
        }
        if( $user['status'] != 0 || $user['storage_status'] != Storage::STATUS_ON )
        {
            $validator-> errors()-> add('error', '用户已经被停用');
            return view('index.login')-> withErrors( $validator );
        }

        unset( $user['password'] );
        unset( $user['remember_token'] );
        unset( $user['deleted_at'] );

        $user['token'] = LoginDao::setCache( $user );
        session([ 'user'=> $user ]);
        return redirect('home');
    }

    public function home()
    {
        return view('index.home');
    }
    public function changePass()
    {
        return view('index.password', [ 'user'=> [] ]);
    }
    public function changePassSave()
    {
        $user = session( 'user' );
        $post = Input::all();
        $validator = Validator::make( $post, [
            'old'    => 'required',
            'new'    => 'required',
            'confirm'=> 'required'
        ], [
            'old.required'    => '旧密码必须填',
            'new.required'    => '新密码必须填',
            'confirm.required'=> '确认密码必须填'
        ]);
        if( $validator-> fails() )
        {
            return view('index.password', [ 'user'=> $post ]);
        }

        if( $post['new'] != $post['confirm'] )
        {
            $validator-> errors()-> add('error', '新密码与确认密码不一致');
            return view('index.password', [ 'user'=> $post ]);
        }
        //验证密码规则
        $passAction = true;
        $rules1 = '/[A-Z]+/';
        $rules2 = '/[a-z]+/';
        $rules3 = '/[0-9]+/';
        $rules4 = '/.{8,}/';
        if( !preg_match( $rules1, $post['new'] )) $passAction = false;
        if( !preg_match( $rules2, $post['new'] )) $passAction = false;
        if( !preg_match( $rules3, $post['new'] )) $passAction = false;
        if( !preg_match( $rules4, $post['new'] )) $passAction = false;

        if( !$passAction )
        {
            $validator-> errors()-> add( 'error', '密码不符合规范，必须包含大小写与数字！并且大于8位' );
            return view( 'user.user_edit', [ 'user'=> $post ])-> withErrors( $validator );
        }

        $re = Users::where( 'id', $user['id'] )
            -> select( 'password' )
            -> first();
        if( empty( $re ))
        {
            $validator-> errors()-> add('error', '用户不存在');
            return view('index.password', [ 'user'=> $post ]);
        }
        if( md5( $post['old'] )  != $re['password'] )
        {
            $validator-> errors()-> add('error', '旧密码不正确');
            return view('index.password', [ 'user'=> $post ]);
        }
        try
        {
            Users::where( 'id', $user['id'] )
                -> update(['password'=> md5( $post['new'] )]);
            return redirect('/password'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            $validator-> errors()-> add('error', '数据保存失败');
            return view('index.password', [ 'user'=> $post ]);
        }
    }

    public function verifyCode()
    {
        $builder = new CaptchaBuilder( getNonceStr(4));
        $builder-> build(140,38);
        session([ 'phrase'=> $builder-> getPhrase()]);//存储验证码
        return response($builder->output())-> header('Content-type','image/jpeg');
    }

    public function ajax()
    {
        $method = Input::get('method');
        switch ( $method )
        {
            case 'city':
                $data = AjaxDao::getCity();
                break;
            case 'town':
                $data = AjaxDao::getTown();
                break;
        }
        return S::jsonReturn( $data );
    }
}