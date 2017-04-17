<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input; //to support input
use App\Http\Requests;
use PushNotification;//To push ios device notification
use DB;
use App\IOSToken;
use Flash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;


class StickerpushController extends Controller
{
    public function getToken(Request $request) {

        // if user open his/her app: it will check device id exist or not
        // if not save device id with token and date else update token and date
        //echo'ok';exit;
        if (isset($_GET["token"])) {

            $token = $_GET["token"];
            $deviceId = $_GET["did"];
            //print_r($queryGet);exit;
            if (IOSToken::where('device_id', '=', Input::get('did'))->exists()) {
                    DB::table('ios_token')
                        ->where('device_id', $deviceId)
                        ->update(array('date_exe' => date('Y-m-d H:i'),'token'=>$token));
                    echo "Response Token Already Exists ";
            }else{
                    $model  = new IOSToken();
                    $model->date_exe = date('Y-m-d H:i');
                    $model->token = $token;
                    $model->device_id = $deviceId;
                    $model->save();
                    echo "Response Token Saved Successfully";
                }

        }else{

           echo 'Token Not Submitted';

        }
    }

    public function pushSticker(Request $request)
    {
     
        $proc_query = DB::connection('sqlsrv')->select('EXEC spGetNewContentNew');
        $page          = Input::get('page', 1);
        $paginate      = 20;
        $offSet              = ($page * $paginate) - $paginate;
        $itemsForCurrentPage = array_slice($proc_query, $offSet, $paginate, true);
        $data                = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, count($proc_query), $paginate, $page);
        //print_r($parsed_json);exit;
        return View('push_sticker',compact('data'));
    }


    /**
     * To send sticker with push notification
     */
    public function pushStickerSend(Request $request)
    {
        
        $message_text = Input::get('message');
        $allNotifiactionData = Input::get('allNotifiactionData');
        //print_r($allNotifiactionData);exit;
        $TokenResult = IOSToken::all()->pluck('token');
        //print_r($TokenResult);exit;
        foreach ($TokenResult as $key => $deviceToken) {


            $message = PushNotification::Message($message_text, array(
                'alert' => $message_text,
                'badge' => 1,
                'sound' => 'default',
                'content-available'=>1,
                'allNotifiactionData' => $allNotifiactionData,
                'launchImage' => "https://upload.wikimedia.org/wikipedia/mediawiki/a/a9/Example.jpg",
                "mediaUrl"=> "https://upload.wikimedia.org/wikipedia/mediawiki/a/a9/Example.jpg",
                "mediaType"=> "image"
            ));
            //print_r($message);exit;
            // Check it is a valid token.
            if( ctype_xdigit($deviceToken) && 64 == strlen($deviceToken)){
                PushNotification::app(['environment' => 'production',
                    'certificate' => app_path().'/ck.pem',
                    'passPhrase'  => '1234',
                    'service'     => 'apns'])->to($deviceToken)->send($message);
            } else {
              dd('invalid device token');
            }

          /*  $aa= PushNotification::app(['environment' => 'development',
                'certificate' => app_path().'/ck.pem',
                'passPhrase'  => '1234',
                'service'     => 'apns'])->to($deviceToken)->send($message);
            print_r($aa);exit;*/

             if (!$message)
               return redirect()->back()->with('status', 'Message not delivered.');
             else
               return redirect()->back()->with('status', 'Message successfully delivered.');
        }
    }
}
