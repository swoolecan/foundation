<?php
declare(strict_types = 1);

namespace Swoolecan\Foundation\Observers;

trait TagTrait
{
    public function _tagSaved($model, $field = 'code')
    {
        if ($model->$field == $model->getOriginal($field)) {
            return true;
        }

        $model->deleteTagInfos(['info_id' => $model->getOriginal($field)]);
        $model->updateTagInfos(['tags' => [$model->$field]]);
        return true;
    }

    public function _tagSaving($model, $field = 'code')
    {
        if ($model->$field == $model->getOriginal($field)) {
            return true;
        }
        $checkCode = $model->getCodeTag($model->$field);
        if (empty($checkCode)) {
            $checkCode = $model->findCreateTag($model->$field);
        }
        $model->code = $checkCode;
        return true;
    }

    public function _tagDeleted($model)
    {
        $model->deleteTagInfos([]);
        return true;
    }
}
