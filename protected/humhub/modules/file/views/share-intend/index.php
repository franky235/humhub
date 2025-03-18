<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\view\components\View;
use humhub\widgets\ModalDialog;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $shareTargets array
 * @var $fileList array
 */
?>

<?php ModalDialog::begin(['header' => Yii::t('FileModule.base', 'Share a file')]) ?>
<div class="modal-body">
    <?php foreach ($shareTargets as $target): ?>
        <a class="btn btn-primary btn-block" data-action-click="ui.modal.load"
           data-action-url="<?= Url::to([$target['route']]) ?>">
            <?= $target['title'] ?>
        </a>
    <?php endforeach; ?>
</div>
<?php ModalDialog::end() ?>
