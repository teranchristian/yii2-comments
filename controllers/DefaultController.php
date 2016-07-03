<?php

namespace yii2mod\comments\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii2mod\comments\models\CommentModel;
use yii2mod\comments\Module;


/**
 * Class DefaultController
 * @package yii2mod\comments\controllers
 */
class DefaultController extends Controller
{
    /**
     * Behaviors
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'update' => ['post'],
                    'delete' => ['post', 'delete']
                ],
            ],
        ];
    }

    /**
     * Create comment.
     * @return array|null|Response
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $entity = urldecode($request->post('entity'));
        Yii::$app->response->format = Response::FORMAT_JSON;
        /* @var $module Module */
        $module = Yii::$app->getModule(Module::$name);
        $commentModelClass = $module->commentModelClass;
        $decryptEntity = Yii::$app->getSecurity()->decryptByKey($entity, $module::$name);
        if ($decryptEntity !== false) {
            $entityData = Json::decode($decryptEntity);
            /* @var $model CommentModel */
            $model = new $commentModelClass;
            $model->setAttributes($entityData);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return ['status' => 'success'];
            } else {
                return [
                    'status' => 'error',
                    'errors' => ActiveForm::validate($model)
                ];
            }
        } else {
            return [
                'status' => 'error',
                'message' => Yii::t('yii2mod.comments', 'Oops, something went wrong. Please try again later.')
            ];
        }
    }

    /**
     * Create comment.
     * @return array|null|Response
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $entity = urldecode($request->post('entity'));
        Yii::$app->response->format = Response::FORMAT_JSON;
        /* @var $module Module */
        $module = Yii::$app->getModule(Module::$name);
        $commentModelClass = $module->commentModelClass;
        $decryptEntity = Yii::$app->getSecurity()->decryptByKey($entity, $module::$name);
        if ($decryptEntity !== false) {
            $entityData = Json::decode($decryptEntity);
            /* @var $model CommentModel */
            $model = $this->findModel($id);
            $model->setAttributes($entityData);
            $model->content = $request->post('CommentModel')["content"];
            if ($model->validate() && $model->save()) {
                return ['status' => 'success'];
            } else {
                return [
                    'status' => 'error',
                    'errors' => ActiveForm::validate($model)
                ];
            }
        } else {
            return [
                'status' => 'error',
                'message' => Yii::t('yii2mod.comments', 'Oops, something went wrong. Please try again later.')
            ];
        }
    }

    /**
     * Delete comment page.
     *
     * @param integer $id Comment ID
     * @return string Comment text
     */
    public function actionDelete($id)
    {
        if ($this->findModel($id)->deleteComment()) {
            return Yii::t('yii2mod.comments', 'Comment was deleted.');
        } else {
            Yii::$app->response->setStatusCode(500);
            return Yii::t('yii2mod.comments', 'Comment has not been deleted. Please try again!');
        }
    }

    /**
     * Find model by ID.
     *
     * @param integer|array $id Comment ID
     * @return null|CommentModel
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        /** @var CommentModel $model */
        $commentModelClass = Yii::$app->getModule(Module::$name)->commentModelClass;
        if (($model = $commentModelClass::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('yii2mod.comments', 'The requested page does not exist.'));
        }
    }
}
