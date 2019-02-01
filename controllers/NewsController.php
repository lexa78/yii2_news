<?php

namespace app\controllers;

use app\models\FileUpload;
use app\models\News;
use app\models\UploadFile;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Cookie;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

class NewsController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['read', 'create', 'update', 'delete', 'activate'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['read'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete', 'activate'],
                        'roles' => ['manager', 'admin'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $pageSize = $request->post('user_news_on_page', null);
            if($pageSize) {
                Yii::$app->response->cookies->add(new Cookie([
                    'name' => 'news_on_page',
                    'value' => $pageSize
                ]));
            } elseif ($request->post('clear', null)) {
                Yii::$app->session->remove('filter');
            } else {
                $filter = [];
                $fieldNames = ['name_filter', 'descr_filter', 'status_filter', 'date_from_filter', 'date_to_filter'];
                if(Yii::$app->session->has('filter')) {
                    $sessionFilter = Yii::$app->session->get('filter');
                    foreach ($fieldNames as $key) {
                        $filter[$key] = $sessionFilter[$key];
                    }
                }
                foreach ($fieldNames as $key) {
                    $filter[$key] = $request->post($key, null);
                    if($key == 'status_filter' and $filter[$key] == -1) {
                        $filter[$key] = null;
                    }
                }
                Yii::$app->session->set('filter', $filter);
            }
        } else {
            if (!isset($request->cookies['news_on_page'])) {
                $pageSize = 10;
                Yii::$app->response->cookies->add(new Cookie([
                    'name' => 'news_on_page',
                    'value' => $pageSize
                ]));
            } else {
                $pageSize = $request->cookies['news_on_page']->value;
            }
        }

        $query = News::find();
        $activeOnly = true;
        if( Yii::$app->user->can('manager') or Yii::$app->user->can('admin')) {
            $activeOnly = false;
        }
        if(Yii::$app->session->has('filter')) {
            if($activeOnly) {
                $query = $query->where('is_active > 0');
                $query = self::getQueryWithFilter($query, true);
            } else {
                $query = self::getQueryWithFilter($query);
            }
        } else {
            if($activeOnly) {
                $query = $query->where('is_active > 0');
            }
        }
        $pages = new Pagination(['totalCount' => $query->count(), 'pageSize' => $pageSize]);
        $news = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('created_at DESC')
            ->all();
        return $this->render('index', compact('news', 'pages', 'pageSize'));
    }

    private static function getQueryWithFilter($query, $andFlag=false){
        $filter = Yii::$app->session->get('filter');
        if(!empty($filter['name_filter'])) {
            if ($andFlag) {
                $query = $query->andWhere(['like', 'title', ':title', [':title' => $filter['name_filter']]]);
            } else {
                $query = $query->where(['like', 'title', ':title', [':title' => $filter['name_filter']]]);
            }
            $andFlag = true;
        }
        if(!empty($filter['descr_filter'])) {
            if ($andFlag) {
                $query = $query->andWhere(['like', 'short_text', ':short_text', [':short_text' => $filter['descr_filter']]]);
            } else {
                $query = $query->where(['like', 'short_text', ':short_text', [':short_text' => $filter['descr_filter']]]);
            }
            $andFlag = true;
        }
        if(isset($filter['status_filter']) and !is_null($filter['status_filter'])) {
            if ($andFlag) {
                $query = $query->andWhere(['is_active' => $filter['status_filter']]);
            } else {
                $query = $query->where(['is_active' => $filter['status_filter']]);
            }
            $andFlag = true;
        }
        if(!empty($filter['date_from_filter']) or !empty($filter['date_to_filter'])) {
            if(!empty($filter['date_from_filter']) and empty($filter['date_to_filter'])) {
                $filter['date_to_filter'] = date('Y-m-d');
            }
            if(empty($filter['date_from_filter']) and !empty($filter['date_to_filter'])) {
                $filter['date_from_filter'] = date('Y-m-d', 2);
            }
            if($andFlag) {
                $query = $query->andWhere('created_at BETWEEN :from AND :to')
                    ->params([':from' => $filter['date_from_filter'], ':to' => $filter['date_to_filter']]);
            } else {
                $query = $query->where('created_at BETWEEN :from AND :to')
                    ->params([':from' => $filter['date_from_filter'], ':to' => $filter['date_to_filter']]);
            }
            $andFlag = true;
        }
        return $query;
    }

    public function actionRead($id = null)
    {
        if (is_null($id)) {
            Yii::$app->session->setFlash('danger', "Новость не найдена");
            return Yii::$app->request->referrer ? $this->redirect(Yii::$app->request->referrer) : $this->goHome();
        }

        $oneNews = News::findOne($id);
        if (is_null($oneNews)) {
            Yii::$app->session->setFlash('danger', "Новость не найдена");
            return Yii::$app->request->referrer ? $this->redirect(Yii::$app->request->referrer) : $this->goHome();
        }

        return $this->render('read', compact('oneNews'));
    }

    public function actionCreate()
    {
        $model = new News();
        $request = Yii::$app->request;
        if ($request->isPost) {
            $tmpFile = UploadedFile::getInstance($model, 'img');
            if($tmpFile) {
                $file = new FileUpload();
                $file->imgFile = $tmpFile;
                if ($fileName = $file->upload()) {
                    $model->load($request->post());
                    $model->img = $fileName;
                } else {
                    foreach ($file->errors as $errors) {
                        foreach ($errors as $err) {
                            Yii::$app->session->setFlash('danger', $err);
                        }
                    }
                    return $this->redirect($request->referrer);
                }
            } else {
                $model->load($request->post());
                $model->img = null;
            }
            if($model->save()) {
                return Yii::$app->response->redirect(array('news/read', 'id' => $model->id));
            } else {
                foreach ($model->errors as $errors) {
                    foreach ($errors as $err) {
                        Yii::$app->session->setFlash('danger', $err);
                    }
                }
                return $this->redirect($request->referrer);
            }
        }
        return $this->render('create', compact('model'));
    }

    public function actionUpdate($id = null)
    {
        $request = Yii::$app->request;
        if (is_null($id)) {
            Yii::$app->session->setFlash('danger', "Новость не найдена");
            return $request->referrer ? $this->redirect($request->referrer) : $this->goHome();
        }
        $model = News::findOne($id);
        if (is_null($model)) {
            Yii::$app->session->setFlash('danger', "Новость не найдена");
            return $request->referrer ? $this->redirect($request->referrer) : $this->goHome();
        }
        if (!Yii::$app->user->can('updateNews', ['news' => $model])) {
            throw new ForbiddenHttpException('Access denied');
        }
        if ($request->isPost) {
            $tmpFile = UploadedFile::getInstance($model, 'img');
            $oldFile = $model->img;
            if($tmpFile) {
                $file = new FileUpload();
                $file->imgFile = $tmpFile;
                if ($fileName = $file->upload()) {
                    $oldFile = FileUpload::getPathToPictures() . DIRECTORY_SEPARATOR . $oldFile;
                    unlink($oldFile);
                    $model->load($request->post());
                    $model->img = $fileName;
                } else {
                    foreach ($file->errors as $errors) {
                        foreach ($errors as $err) {
                            Yii::$app->session->setFlash('danger', $err);
                        }
                    }
                    return $this->redirect($request->referrer);
                }
            } else {
                $model->load($request->post());
                $model->img = null;
            }
            if($model->save()) {
                return Yii::$app->response->redirect(['news/read', 'id' => $model->id]);
            } else {
                foreach ($model->errors as $errors) {
                    foreach ($errors as $err) {
                        Yii::$app->session->setFlash('danger', $err);
                    }
                }
                return $this->redirect($request->referrer);
            }
        }
        return $this->render('update', compact('model'));
    }

    public function actionDelete($id = null)
    {
        $request = Yii::$app->request;
        if (is_null($id)) {
            Yii::$app->session->setFlash('danger', "Новость не найдена");
            return $request->referrer ? $this->redirect($request->referrer) : $this->goHome();
        }
        $model = News::findOne($id);
        if (is_null($model)) {
            Yii::$app->session->setFlash('danger', "Новость не найдена");
            return $request->referrer ? $this->redirect($request->referrer) : $this->goHome();
        }
        if (!Yii::$app->user->can('updateNews', ['news' => $model])) {
            throw new ForbiddenHttpException('Access denied');
        }
        if ($request->isPost) {
            $file = FileUpload::getPathToPictures() . DIRECTORY_SEPARATOR . $model->img;
            if(is_file($file)) {
                unlink($file);
            }
            $model->delete();
            return $this->redirect(['news/index']);
        } else {
            Yii::$app->session->setFlash('danger', "Удалить можно только Post запросом");
            return $request->referrer ? $this->redirect($request->referrer) : $this->goHome();
        }
    }

    public function actionActivate($id = null)
    {
        if (is_null($id)) {
            exit(json_encode(['error' => 'Новость не найдена']));
        }
        $model = News::findOne($id);

        if (is_null($model)) {
            exit(json_encode(['error' => 'Новость не найдена']));
        }
        if (!Yii::$app->user->can('updateNews', ['news' => $model])) {
            exit(json_encode(['error' => 'Нет доступа к этой новости']));
        }
        $model->is_active = intval(! boolval($model->is_active));
        if($model->save()) {
            if($model->is_active) {
                $addClass = 'btn-success';
                $removeClass = 'btn-danger';
            } else {
                $addClass = 'btn-danger';
                $removeClass = 'btn-success';
            }
            exit(json_encode(['success' => ['add_class' => $addClass, 'remove_class' => $removeClass, 'id' => $id]]));
        } else {
            $errArr = [];
            foreach ($model->errors as $errors) {
                foreach ($errors as $err) {
                    $errArr['error'][] = $err;
                }
            }
            exit(json_encode($errArr));
        }
    }
}
