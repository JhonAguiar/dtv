<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Esta clase es para la administración de documentos. 
 * @Autor <achavezb@directvla.com.co>
 */
class DocumentsController extends Controller
{
    
    protected $controller;
    protected $method;
    protected $video = '';

    # -----------------------------------------------------------------------------
    public function __construct() {
        $this->middleware('auth');
        if(!\App::runningInConsole()){
            $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
            $parts = explode('@', $controller);
            $this->controller = substr($parts[0], 0, -10);
            $this->method = $parts[1];
            if ( file_exists('videos/'.$this->controller.'_'.$this->method.'.mp4') ) {
                $this->video = url('videos/'.$this->controller.'_'.$this->method.'.mp4');
            }
        }
    }

    # -----------------------------------------------------------------------------
    public function index(){
        if(view()->exists('documents.index')) {
            return view('documents.index', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000095'),// Listado de documentos ROSS
                    'video' => $this->video,
                ],
            ]);   
        }
    }

    # -----------------------------------------------------------------------------
    public function openDocument( $file='' ){
        if(view()->exists('documents.open')) {
            $file_open = 'documents/'.$file;
            if ( !empty($file) and file_exists($file_open) ) {
                return view('documents.open', [
                    'headers' => [
                        'controller' => $this->controller,
                        'method' => $this->method,
                        'title' => trans('messages.000096').' '.$file,// Documento
                        'video' => $this->video,
                    ],
                    'file_open' => $file_open
                ]); 
            }
            return redirect()->back();            
        }else{
            return view('errors.404', []);
        }
    }


}
