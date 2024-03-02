<?php


namespace App\Services;

use App\Enums\MediaTypeEnum;
use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService{

    const STORAGE_DIR = 'files'; #Without trailing Slash

    public function __construct(){

    }

    public function uploadAndAttachPdfToModel(Model $model,?UploadedFile $file,string $key)
    {
        if(empty($file)) return $this->removePreviousMediaIfExists($model,$key);
        $path = self::STORAGE_DIR.'/'.str(class_basename($model))->plural()->lower()->slug().'/'.Str::slug($key).'/'.$file->hashName();
        $this->removePreviousMediaIfExists($model,$key);
        Storage::disk(config('filesystems.default'))->put($path,file_get_contents($file));
        $media = new Media();
        $media->mediable_id = $model->id;
        $media->mediable_type = get_class($model);
        $media->mediable_type = get_class($model);
        $media->type = MediaTypeEnum::DOCUMENT_PDF;
        $media->size = $file->getSize();
        $media->disk = config('filesystems.default');
        $media->original_name = $file->getClientOriginalName();
        $media->path = $path;
        $media->key = $key;
        $media->save();
    }

    public function uploadAndAttachImageToModel(Model $model,?UploadedFile $file,string $key)
    {
        if(empty($file)) return $this->removePreviousMediaIfExists($model,$key);
        $path = self::STORAGE_DIR.'/'.str(class_basename($model))->plural()->lower()->slug().'/'.Str::slug($key).'/'.$file->hashName();
        $this->removePreviousMediaIfExists($model,$key);
        Storage::disk(config('filesystems.default'))->put($path,file_get_contents($file));
        $media = new Media();
        $media->mediable_id = $model->id;
        $media->mediable_type = get_class($model);
        $media->mediable_type = get_class($model);
        $media->type = MediaTypeEnum::IMAGE_JPEG;
        $media->size = $file->getSize();
        $media->disk = config('filesystems.default');
        $media->original_name = $file->getClientOriginalName();
        $media->path = $path;
        $media->key = $key;
        $media->save();
    }

    public function removePreviousMediaIfExists(Model $model,$key)
    {
        $media = $model->$key()->where('key',$key)->first();
        if($media === null) return false;

        if(Storage::exists($media->path))
        {
            Storage::delete($media->path);
        }
        $media->delete();
    }
}

