<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\forms\ShareIntendTargetForm;
use humhub\modules\content\services\ContentCreationService;
use humhub\modules\space\helpers\CreateContentPermissionHelper;
use Yii;
use yii\web\HttpException;

/**
 * Allows sharing files from the mobile app
 *
 * @since 1.17.2
 */
abstract class ShareIntendController extends Controller
{
    abstract public function actionCreate();

    abstract protected function getCreatePermissionClass(): string;

    private const SESSION_KEY_TARGET_GUID = 'shareIntendTargetGuid';

    public ?ContentContainerActiveRecord $shareTarget = null;

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        \humhub\modules\file\controllers\ShareIntendController::checkShareFileGuids();

        if (!in_array($action->id, ['index', 'space-search-json'])) {
            $this->initShareTarget();
        }
        return true;
    }

    private function initShareTarget() : void
    {
        $shareTargetGuid = Yii::$app->session->get(self::SESSION_KEY_TARGET_GUID);
        $this->shareTarget = ContentContainer::findRecord($shareTargetGuid);
        if ($this->shareTarget === null) {
            throw new HttpException('500', 'No target to share found!');
        }
    }

    public function actionIndex()
    {
        Yii::$app->session->remove(self::SESSION_KEY_TARGET_GUID);

        $model = new ShareIntendTargetForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->set(self::SESSION_KEY_TARGET_GUID, $model->targetSpaceGuid);
            $this->initShareTarget();

            return $this->actionCreate();
        }

        return $this->renderAjax('@content/views/share-intend/index', [
            'model' => $model,
        ]);
    }

    public function actionSpaceSearchJson()
    {
        $spaces = CreateContentPermissionHelper::findSpaces(
            $this->getCreatePermissionClass(),
            Yii::$app->request->get('keyword'),
            Yii::$app->user->identity
        );
        return $this->asJson($spaces);
    }
}
