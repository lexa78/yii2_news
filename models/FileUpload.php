<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class FileUpload extends Model
{
    /**
     * @var UploadedFile
     */
    public $imgFile;

    public function rules()
    {
        return [
            [['imgFile'], 'file', 'extensions' => 'png, jpg, gif'],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $fileName = self::getPathToPictures();
            if(!file_exists($fileName)) {
                mkdir($fileName, 0644);
            }
            $fileName .= DIRECTORY_SEPARATOR . time() . '.' . $this->imgFile->extension;
            $this->imgFile->saveAs($fileName);
            return end(explode(DIRECTORY_SEPARATOR, $fileName));
        } else {
            return false;
        }
    }

    public static function getPathToPictures()
    {
        return Yii::$app->basePath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'pictures';
    }
}