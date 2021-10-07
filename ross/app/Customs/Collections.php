<?php 
namespace App\Customs;

# NOTA
#Para usar: #use App\Customs\Collections as objCollections;
class Collections 
{

	# -----------------------------------------------------------------------------
	protected static function collectionRegions()
	{
		return [
		    ['id' => 'Arg', 'name'=>'Argentina', 'language' => 'es', 'status' => 'active', 'timezone' => 'America/Argentina/Buenos_Aires'],
		    ['id' => 'Bra', 'name'=>'Brasil', 'language' => 'po', 'status' => 'inactive', 'timezone' => 'America/Sao_Paulo'],
		    ['id' => 'Chi', 'name'=>'Chile', 'language' => 'es', 'status' => 'inactive', 'timezone' => 'America/Santiago'],
		    ['id' => 'Col', 'name'=>'Colombia', 'language' => 'es', 'status' => 'active', 'timezone' => 'America/Bogota'],
		    ['id' => 'Ecu', 'name'=>'Ecuador', 'language' => 'es', 'status' => 'inactive', 'timezone' => 'America/Guayaquil'],
		    ['id' => 'Usa', 'name'=>'Estados Unidos', 'language' => 'en', 'status' => 'inactive', 'timezone' => ''],
		];
	}


	# -----------------------------------------------------------------------------
    public static function getCollectionAllRegions() {
		$collection = Collections::collectionRegions();
		$data = collect( $collection )->where('status', 'active');
		return $data;
    }

    # -----------------------------------------------------------------------------
    public static function getCollectionRegionsById( $id='' ) {
		$collection = Collections::collectionRegions();
		$data = collect( $collection )->where('id', $id);
		foreach ($data as $key => $val) {
			$array = $val;
		}
		return (object)$array;
    }

    # -----------------------------------------------------------------------------
    public static function getCollectionRegionsActive() {
		$collection = Collections::collectionRegions();
		return collect( $collection )->where('status', 'active');
    }

    # -----------------------------------------------------------------------------
    public static function getLanguage() {
		return [
			'es' => trans('messages.es'),
			'en' => trans('messages.en'),
			//'po' => trans('messages.po'),
		];
    }
}