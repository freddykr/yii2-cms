<?php

namespace frontend\modules\login\controllers;

use common\models\Constants;
use common\models\forms\LoginForm;
use common\models\forms\PasswordResetRequestForm;
use common\models\forms\ResetPasswordForm;
use common\models\forms\UserForm;
use common\models\forms\VisitForm;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

/**
 * Default controller for the `login` module
 */
class DefaultController extends Controller
{
    // информация о текущей странице
    public $page;

    public function init()
    {
        parent::init();
        $this->page = (new \yii\db\Query())
            ->select(['*'])
            ->from('document')
            ->where(['alias' => $this->module->id])
            ->one();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => Yii::$app->userAccess->getUserAccess($this->page['access'])
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws ErrorException
     */
    public function beforeAction($action)
    {
        try {
            parent::beforeAction($action);
        } catch (BadRequestHttpException $e) {
            Yii::$app->errorHandler->logException($e);
            throw new ErrorException($e->getMessage());
        }

        if ($alias = Yii::$app->request->get('alias')) {
            $data = (new \yii\db\Query())
                ->select(['*'])
                ->from('document')
                ->where([
                    'alias' => $alias,
                    'parent_id' => $this->page['id'],
                ])
                ->one();
            $document_id = $data['id'];
        } else {
            $document_id = $this->page['id'];
        }

        // контроль посещений страниц
        if (Yii::$app->user->isGuest) {
            // с одним IP обновляется раз в сутки
            $data = (new \yii\db\Query())
                ->select(['*'])
                ->from('visit')
                ->where([
                    'document_id' => $document_id,
                    'ip' => Yii::$app->request->userIP,
                    'user_agent' => Yii::$app->request->userAgent
                ])
                ->andWhere(['>', 'created_at', time() - 24*60*60])
                ->one();
            if ($data == false) {
                $modelVisitForm = new VisitForm();
                $modelVisitForm->created_at = time();
                $modelVisitForm->document_id = $document_id;
                $modelVisitForm->ip = Yii::$app->request->userIP;
                $modelVisitForm->user_agent = Yii::$app->request->userAgent;
                $modelVisitForm->save();
            }
        } else {
            $data = (new \yii\db\Query())
                ->select(['*'])
                ->from('visit')
                ->where([
                    'document_id' => $document_id,
                    'user_id' => Yii::$app->user->id
                ])
                ->one();
            if (!$data) {
                $modelVisitForm = new VisitForm();
                $modelVisitForm->created_at = time();
                $modelVisitForm->document_id = $document_id;
                $modelVisitForm->ip = Yii::$app->request->userIP;
                $modelVisitForm->user_agent = Yii::$app->request->userAgent;
                $modelVisitForm->user_id = Yii::$app->user->id;
                $modelVisitForm->save();
            }
        }

        return true;
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        // Уже авторизированных отправляем на домашнюю страницу
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $modelLoginForm = new LoginForm();
        if ($modelLoginForm->load(Yii::$app->request->post())/* && $modelLoginForm->login()*/) {
            $user = (new \yii\db\Query())
                ->select(['*'])
                ->from('user')
                ->where(['email' => $modelLoginForm->email])
                ->one();
            if ($user['status'] == Constants::STATUS_WAIT) {
                Yii::$app->session->set(
                    'message',
                    [
                        'type'      => 'success',
                        'icon'      => 'glyphicon glyphicon-ok',
                        'message'   => Yii::t('app', 'Необходимо подтвердить ваш емайл.'),
                    ]
                );
                return $this->redirect(Url::to(['/signup/default/confirm-email', 'user_id' => $user['id']]));
            }
            if ($modelLoginForm->login()) {
                return $this->goHome();
            }
        }

        if (!Yii::$app->request->isPjax || !Yii::$app->request->isAjax) {
            return $this->goHome();
        }

        if ($modelLoginForm->errors) {
            return $this->renderAjax('@frontend/views/templates/login/_login-form', [
                'page' => $this->page,
                'modelLoginForm' => $modelLoginForm,
            ]);
        }

        return $this->renderAjax('@frontend/views/templates/login/login', [
            'page' => $this->page,
            'modelLoginForm' => $modelLoginForm,
        ]);
    }

    /**
     * Запрос на сброс пароля.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        if (!Yii::$app->request->isPjax || !Yii::$app->request->isAjax) {
            return $this->goHome();
        }

        $modelPasswordResetRequestForm = new PasswordResetRequestForm();
        if ($modelPasswordResetRequestForm->load(Yii::$app->request->post())) {
            if ($modelPasswordResetRequestForm->validate()) {
                if ($modelPasswordResetRequestForm->sendEmail()) {
                    Yii::$app->session->set(
                        'message',
                        [
                            'type'      => 'success',
                            'icon'      => 'glyphicon glyphicon-envelope',
                            'message'   => ' '.Yii::t('app', 'Проверьте вашу электронную почту и следуйте дальнейшим инструкциям.'),
                        ]
                    );
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->set(
                        'message',
                        [
                            'type'      => 'danger',
                            'icon'      => 'glyphicon glyphicon-envelope',
                            'message'   => ' '.Yii::t('app', 'К сожалению, мы не можем сбросить пароль для введенной электронной почты.'),
                        ]
                    );
                    return $this->renderAjax('@frontend/views/templates/login/_request-password-reset-token-form', [
                        'modelPasswordResetRequestForm' => $modelPasswordResetRequestForm,
                    ]);
                }
            }
        }

        if ($modelPasswordResetRequestForm->errors) {
            Yii::$app->session->set(
                'message',
                [
                    'type'      => 'danger',
                    'icon'      => 'glyphicon glyphicon-envelope',
                    'message'   => $modelPasswordResetRequestForm->getFirstError('email'),
                ]
            );
            return $this->renderAjax('@frontend/views/templates/login/_request-password-reset-token-form', [
                'modelPasswordResetRequestForm' => $modelPasswordResetRequestForm,
            ]);
        }

        return $this->renderAjax('@frontend/views/templates/login/request-password-reset-token', [
            'modelPasswordResetRequestForm' => $modelPasswordResetRequestForm,
        ]);
    }

    /**
     * Сброс пароля
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $modelResetPasswordForm = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($modelResetPasswordForm->load(Yii::$app->request->post()) && $modelResetPasswordForm->validate() && $modelResetPasswordForm->resetPassword()) {
            Yii::$app->session->set(
                'message',
                [
                    'type'      => 'success',
                    'icon'      => 'glyphicon glyphicon-ok',
                    'message'   => Yii::t('app', 'Новый пароль сохранен.'),
                ]
            );
            return $this->goHome();
        }
        return $this->render('@frontend/views/templates/login/resetPassword', [
            'modelResetPasswordForm' => $modelResetPasswordForm,
        ]);
    }
}
