<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Adldap\Laravel\Facades\Adldap;

class ExampleController extends Controller
{

    public function sqlbog(){
        #echo phpinfo();die;
        #if(\DB::connection()->getDatabaseName()){
        #    echo "connected successfully to database ".\DB::connection()->getDatabaseName();
        #}

        $info = \DB::connection('sqlsrv_sqlbog')->select('select top 5 * from tbhistorico')->getPdo();
        echo "<pre>";
        print_r($info);
        die('code...');
    }


    public function soap( ){
        # -----------------------------------------------------------------------------
        # Recurso NUSOAP tomado de:
        # https://packagist.org/packages/econea/nusoap
        # https://github.com/econea/nusoap
        
        // Ejemplo:
        $client = new \nusoap_client('http://10.165.1.24/BroadbandDev/Colombia/BOG/LTE?wsdl', true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = FALSE;
        $params = array("subscriber_identity"=>'732176000246992', "technology"=>"?");
        $result = $client->call('Provisioning.read', $params);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        die("fin del consumo del servicio.");
    }


    public function ldap(){
        // https://adldap2.github.io/Adldap2/#/
        $ad = new \Adldap\Adldap();
        $config = [  
          'hosts'    => ['vrio.isp'],
          'base_dn'  => 'dc=vrio,dc=isp',
          'username' => 'user_ross@vrio.isp',
          'password' => 'Netxweek*96',
        ];
        $ad->addProvider($config);
        try {
            $provider = $ad->connect();
            #$results = $provider->search()->where('cn', '=', 'Alfonso')->get();
            $user = $provider->search()->find('achavez');
            #echo "<pre>";
            #print_r($users);
            #echo "</pre>";
            #die("pruebas");

            $users = $provider->search()->users()->paginate()->getResults();
            $array=array();
            $all_users=array();
            foreach ($users as $key => $value) {
                $array['group_id'] = $value['primarygroupid'][0];
                $array['country_code'] = $value['countrycode'][0];
                $array['username'] = $value['samaccountname'][0];
                $array['name'] = $value['givenname'][0];
                $array['lastname'] = $value['sn'][0];
                $array['fullname'] = $value['cn'][0];
                $array['email'] = $value['userprincipalname'][0];
                $array['created_at'] = $value['whencreated'][0];
                $array['updated_at'] = $value['whenchanged'][0];
                array_push($all_users, $array);
            }
            echo "<pre>";
            print_r($all_users);
            echo "</pre>";
            die;

        } catch (\Adldap\Auth\BindException $e) {
            // There was an issue binding / connecting to the server.
        }
    }


    public function FunctionName( Request $request ){
        $url = $request->url();
        $errors=array();
        return redirect()->back()->withErrors($errors)->withInput();
        return redirect()->back()->with('danger', 'unsolo mensaje con un estado.')->withInput();
    }

    public function slug($original){
        $slug = str_replace(" ", "-", $original);
        $slug = preg_replace('/[^\w\d\-\_]/i', '', $slug);
        return strtolower($slug);
    }



    public function HeaderPdf( $titulo=null ){
        # Tamaño carta
        # Largo: 216
        # Alto: 279
        $obj = new \App\Http\Controllers\ResourcesController;
        $hoy = $obj->getStringDate();

        if(\Fpdf::PageNo() >1){
            #una portada
        }

        # Fondo de página.
        \Fpdf::Image(url('img/fondo.png'), 0, 0, 216, 280);

        # Logo de página.
        \Fpdf::Image(url('img/logo-blue.png'), 10, 5, 60, 10);

        # Linea vertical
        \Fpdf::SetLineWidth(0.3);
        \Fpdf::SetDrawColor( 108, 118, 128 );
        \Fpdf::Line( 80, 2, 80, 18 );

        \Fpdf::setY(4); 
        \Fpdf::SetFont('Arial', 'I', 14);
        \Fpdf::SetTextColor( 108, 118, 128 );
        \Fpdf::MultiCell(186, 5, $titulo, 0, 'R', false);
        
        \Fpdf::SetFont('Arial', 'I', 10);
        \Fpdf::MultiCell(186, 5, env('URL_DOMINIO', ''), 0, 'R', false);
        \Fpdf::MultiCell(186, 5, $hoy, 0, 'R', false);

        # Pie de página.
        \Fpdf::setY(258);
        \Fpdf::SetFont('Arial', '', 10);
        \Fpdf::SetTextColor(99, 108, 132);
        $page= 'Pag. '.\Fpdf::PageNo().' de {nb} | '.$hoy.' '.date("H:m");
        \Fpdf::Cell(0, 10, $page, 0, 1, 'C');
    }
    public function verpdf(){
        header('Content-type: application/pdf');
        /*\Fpdf::AddPage('P', 'Letter', 0);
        \Fpdf::SetFont('Courier', 'B', 18);
        \Fpdf::Cell(50, 25, 'Hello World!');
        #\Fpdf::Output('Anuncio.pdf', 'D', true);
        \Fpdf::Output();
        exit(0);*/


        #http://www.fpdf.org/

        $titulo="ROSS - Regional Operations Support System";
        $texto="Este es un texto de explicación.";

        # Iniciar el PDF.
        \Fpdf::AddPage('P', 'Letter', 0);
        \Fpdf::AliasNbPages();
        \Fpdf::SetMargins(15, 20, 15);
        \Fpdf::SetAutoPageBreak(true, 10);  
        \Fpdf::SetTitle( $titulo , true);
        \Fpdf::SetAuthor('Ing Alfonso Chávez', true);
        \Fpdf::SetSubject('DATOS DEL ANUNCIO', true);
        \Fpdf::SetCreator('CREADOR', true);
        
        # Dibuja la cabecera de la página.
        $this->HeaderPdf( $titulo );

        # Setear el punto inicial de contenido.
        \Fpdf::setXY(10, 26); 
        \Fpdf::SetTextColor( 47, 64, 80 );
        \Fpdf::SetFillColor(206, 208, 214);

        # El titulo del anuncio.
        \Fpdf::setX(10); 
        \Fpdf::SetFont('Arial', 'I', 14);
        \Fpdf::MultiCell( 196, 8, $titulo, 0, 'L', false);
        
        # La descripción del anuncio.
        \Fpdf::SetTextColor( 98, 106, 120 );
        \Fpdf::setX(10); 
        \Fpdf::SetFont('Arial', '', 10);
        $texto=substr($texto, 0, 910);
        \Fpdf::MultiCell( 196, 5, utf8_decode($texto), 0, 'J', false);

        \Fpdf::Output();
        exit(0);

    }
}
