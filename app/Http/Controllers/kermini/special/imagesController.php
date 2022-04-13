<?php

namespace App\Http\Controllers\kermini\special;

use App\Http\Controllers\Controller;
use App\Models\images;
use App\Models\Scrgb_Image_Requests;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Nette\Utils\DateTime;
use TimeHunter\LaravelGoogleReCaptchaV3\Validations\GoogleReCaptchaV3ValidationRule;

class imagesController extends Controller
{
    /**
     * @var int How many images should be displayed on the media's page.
     */
    private $howmanyimages = 8;

    /**
     * Function handling the display of the media's page.
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function showImages($pageid=NULL, Request $request){

        if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmimages = images::where('user_id', $request->user()->id)->count();
        $buttons = ceil($hmimages / $this->howmanyimages);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $images = images::where('user_id', $request->user()->id)->get()->reverse()
            ->splice(($pageid - 1) * $this->howmanyimages, $this->howmanyimages);

        return view('images.dashboard',compact(
            'images',
            'buttons',
            'pageid'
        ));
    }

    /**
     * Function handling the upload of an image
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function postImage(Request $request){
        $validator = Validator::make($request->all(), [
            'image' => 'required|mimes:m4v,avi,flv,mp4,mov,jpg,jpeg,png,bmp,gif,svg,webp',
            'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('postImage')]
        ]);

        if ($validator->fails()) {
            return redirect()->route('showImages')->with(
                'status', 'Query invalid'
            )->withErrors($validator);
        }else {
            //do stuff to upload
            $fileName = Str::random(rand(20,25)) .
                            "." .
                            str::afterLast($request->image->getMimeType(),"/");
            $filePath = "kdrm_img/";


            if(
                Storage::putFileAs(
                    $filePath,
                    $request->image,
                    $fileName
                )
            ){
                $img = new images;
                $img->referencePath = $filePath . $fileName;
                $img->fileName = $fileName;
                $img->fileSize = $request->image->getSize();
                $img->user_id = Auth::user()->id; //ratio bozo

                $img->save();

                $msg = "Image successfully uploaded";
            }
            else{
                $msg = "File Upload failed";
            }

            return redirect()->route('showImages')->with(
                'status', $msg
            );
        }
    }

    /**
     * Function handling image deletion
     *
     * @param $imageid
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteImage($imageid, Request $request){
        if(
            $imageid!=null
            and !is_numeric($imageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        if(
            images::where('id', $imageid)->count() == 1 &&
            images::where('id', $imageid)->first()->user_id == $request->user()->id
        ){
            Storage::delete(
                images::where('id', $imageid)->first()->referencePath
            );

            images::where('id', $imageid)->delete();

            $msg = "Image deleted";
        }
        else{
            $msg = "Image was not deleted";
        }

        return redirect()->route('showImages')->with(
            'status', $msg
        );
    }

    public function saveScreenGrab($imagekey, Request $request){
        $image = $request->post('d');

        $requestSCRGB = Scrgb_Image_Requests::where('SCRGBimagekey', $imagekey);
        $currentDate = new DateTime( date('Y-m-d H:i:s') );

        if(
            $requestSCRGB->count() == 1 &&
            $requestSCRGB->RequestValidFor_Seconds >= (
                $currentDate->getTimestamp() - (new DateTime( $requestSCRGB->first()->created_at ))->getTimestamp()
            )
        ){
            $fileName = Str::random(rand(20,25)) .
                "." .
                str::afterLast($image->getMimeType(),"/");
            $filePath = "kdrm_img/";


            if(
                Storage::putFileAs(
                    $filePath,
                    $image,
                    $fileName
                )
            ){
                $img = new images;
                $img->referencePath = $filePath . $fileName;
                $img->fileName = $fileName;
                $img->fileSize = $request->image->getSize();
                $img->user_id = $requestSCRGB->first()->user_id; //ratio bozo

                $img->save();

                $requestSCRGB->used = 1;
                $requestSCRGB->save();
                $requestSCRGB->touch();
            }
        }

        return "
            local drmlicense = '".Str::random(30)."'
        ";
    }

}
