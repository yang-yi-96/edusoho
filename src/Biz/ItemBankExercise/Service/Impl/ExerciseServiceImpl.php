<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\Exception\UnableJoinException;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\Dao\ExerciseModuleDao;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\OperateReason;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\System\Service\LogService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class ExerciseServiceImpl extends BaseService implements ExerciseService
{
    public function update($id, $fields)
    {
        return $this->getExerciseDao()->update($id, $fields);
    }

    public function create($exercise)
    {
        if (!ArrayToolkit::requireds($exercise, ['questionBankId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        try {
            $this->beginTransaction();

            $exercise['creator'] = $this->getCurrentUser()->getId();
            $exercise = $this->getExerciseDao()->create($exercise);
            $this->getExerciseMemberService()->addTeacher($exercise['id']);
            $this->createChapterModule($exercise);
            $this->getExerciseModuleService()->createAssessmentModule($exercise['id'], '模拟考试');

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $exercise;
    }

    protected function createChapterModule($exercise)
    {
        $scene = $this->getAnswerSceneService()->create(
            [
                'name' => '章节练习',
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => 0,
                'enable_facein' => 0,
                'pass_score' => 0,
                'manual_marking' => 1,
                'start_time' => 0,
                'doing_look_analysis' => 1,
            ]
        );
        $this->getItemBankExerciseModuleDao()->create([
            'exerciseId' => $exercise['id'],
            'title' => '章节练习',
            'type' => 'chapter',
            'answerSceneId' => $scene['id'],
        ]);
    }

    public function get($exerciseId)
    {
        return $this->getExerciseDao()->get($exerciseId);
    }

    public function count($conditions)
    {
        return $this->getExerciseDao()->count($conditions);
    }

    public function findByIds($ids)
    {
        $itemBankExercises = $this->getExerciseDao()->findByIds($ids);

        return ArrayToolkit::index($itemBankExercises, 'id');
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getExerciseDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function tryManageExercise($exerciseId = 0, $teacher = 1)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $exercise = $this->getExerciseDao()->get($exerciseId);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        if (!$this->hasExerciseManagerRole($exerciseId, $teacher)) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_MANAGE_EXERCISE());
        }

        return $exercise;
    }

    public function hasExerciseManagerRole($exerciseId = 0, $teacher = 1)
    {
        $user = $this->getCurrentUser();
        //未登录，无权限管理
        if (!$user->isLogin()) {
            return false;
        }

        //不是管理员，无权限管理
        if ($this->hasAdminRole()) {
            return true;
        }

        $exercise = $this->get($exerciseId);
        //课程不存在，无权限管理
        if (empty($exercise)) {
            return false;
        }

        if (0 == $teacher || in_array($user->getId(), $exercise['teacherIds'])) {
            return true;
        }

        return false;
    }

    public function updateExerciseStatistics($id, $fields)
    {
        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $updateFields = [];
        foreach ($fields as $field) {
            if ('studentNum' === $field) {
                $updateFields['studentNum'] = $this->countStudentsByExerciseId($id);
            }
        }

        if (empty($updateFields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $exercise = $this->getExerciseDao()->update($id, $updateFields);

        return $exercise;
    }

    public function countStudentsByExerciseId($exerciseId)
    {
        return $this->getExerciseMemberDao()->count(
            [
                'exerciseId' => $exerciseId,
                'role' => 'student',
            ]
        );
    }

    public function isExerciseTeacher($exerciseId, $userId)
    {
        $member = $this->getExerciseDao()->get($exerciseId);

        return !empty($member) && in_array($userId, $member['teacherIds']);
    }

    public function changeExerciseCover($id, $coverArray)
    {
        if (empty($coverArray)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $exercise = $this->tryManageExercise($id);
        $covers = [];
        foreach ($coverArray as $cover) {
            $file = $this->getFileService()->getFile($cover['id']);
            $covers[$cover['type']] = $file['uri'];
        }

        $exercise = $this->getExerciseDao()->update($exercise['id'], ['cover' => $covers]);

        return $exercise;
    }

    public function getByQuestionBankId($questionBankId)
    {
        return $this->getExerciseDao()->getByQuestionBankId($questionBankId);
    }

    public function searchOrderByStudentNumAndLastDays($conditions, $lastDays, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getExerciseDao()->searchOrderByStudentNumAndLastDays($conditions, $lastDays, $start, $limit);
    }

    public function searchOrderByRatingAndLastDays($conditions, $lastDays, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getExerciseDao()->searchOrderByRatingAndLastDays($conditions, $lastDays, $start, $limit);
    }

    public function updateModuleEnable($exercised, $enable)
    {
        return $this->getExerciseDao()->update($exercised, $enable);
    }

    public function updateBaseInfo($id, $fields)
    {
        $exercise = $this->tryManageExercise($id);

        $fields = $this->validateExpiryMode($fields);
        $fields = $this->processFields($exercise, $fields);
        $exercise = $this->getExerciseDao()->update($id, $fields);

        return $exercise;
    }

    public function deleteExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);

        $this->getExerciseDao()->delete($exerciseId);

        $user = $this->getCurrentUser();

        $this->getLogService()->info('item_bank_exercise', 'delete_exercise', "删除练习{$user['nickname']}(#{$user['id']})");
    }

    public function recommendExercise($exerciseId, $number)
    {
        $this->tryManageExercise($exerciseId);
        if (!is_numeric($number)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $fields = [
            'recommended' => 1,
            'recommendedSeq' => (int) $number,
            'recommendedTime' => time(),
        ];

        $exercise = $this->getExerciseDao()->update($exerciseId, $fields);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'recommend_exercise', "推荐练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function cancelRecommendExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);
        $fields = [
            'recommended' => 0,
            'recommendedTime' => 0,
            'recommendedSeq' => 0,
        ];

        $exercise = $this->getExerciseDao()->update($exerciseId, $fields);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'cancel_recommend_exercise', "取消推荐练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function publishExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);

        $exercise = $this->getExerciseDao()->update($exerciseId, ['status' => 'published']);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'publish_exercise', "发布练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function closeExercise($exerciseId)
    {
        $this->tryManageExercise($exerciseId);

        $exercise = $this->getExerciseDao()->update($exerciseId, ['status' => 'closed']);

        $user = $this->getCurrentUser();
        $this->getLogService()->info('item_bank_exercise', 'close_exercise', "关闭练习{$user['nickname']}(#{$user['id']})");

        return $exercise;
    }

    public function canTakeItemBankExercise($exerciseId)
    {
        $exercise = $this->get($exerciseId);

        if (empty($exercise)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        $member = $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']);

        if ($member && in_array($member['role'], ['teacher', 'student'])) {
            return true;
        }

        if ($user->hasPermission('admin_v2_item_bank_exercise')) {
            return true;
        }

        return false;
    }

    protected function validateExpiryMode($exercise)
    {
        $expiryMode = ExpiryModeFactory::create($exercise['expiryMode']);

        return $expiryMode->validateExpiryMode($exercise);
    }

    private function processFields($exercise, $fields)
    {
        if (in_array($exercise['status'], ['published', 'closed'])) {
            //发布或者关闭，不允许修改模式，但是允许修改时间
            unset($fields['expiryMode']);
            if ('published' == $exercise['status']) {
                //发布后，不允许修改时间
                unset($fields['expiryDays']);
                unset($fields['expiryStartDate']);
                unset($fields['expiryEndDate']);
            }
        }

        if (empty($fields['price']) || $fields['price'] <= 0) {
            $fields['isFree'] = 1;
        } else {
            $fields['isFree'] = 0;
        }

        return $fields;
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->biz->service('Content:FileService');
    }

    protected function hasAdminRole()
    {
        $user = $this->getCurrentUser();

        return $user->hasPermission('admin_v2_item_bank_exercise_content_manage');
    }

    public function canLearnExercise($exerciseId)
    {
        return $this->biz['item_bank_exercise.learn_chain']->process($this->get($exerciseId));
    }

    public function canJoinExercise($exerciseId)
    {
        return $this->biz['item_bank_exercise.join_chain']->process($this->get($exerciseId));
    }

    public function freeJoinExercise($exerciseId)
    {
        $access = $this->canJoinExercise($exerciseId);
        if (AccessorInterface::SUCCESS != $access['code']) {
            throw new UnableJoinException($access['msg'], $access['code']);
        }

        $exercise = $this->get($exerciseId);

        if ((1 == $exercise['isFree'] || 0 == $exercise['originPrice']) && $exercise['joinEnable']) {
            return $this->getExerciseMemberService()->becomeStudent(
                $exercise['id'],
                $this->getCurrentUser()->getId(),
                [
                    'remark' => OperateReason::JOIN_BY_FREE,
                    'reason' => OperateReason::JOIN_BY_FREE,
                    'reasonType' => OperateReason::JOIN_BY_FREE_TYPE,
                ]
            );
        }
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        return $conditions;
    }

    public function findExercisesByLikeTitle($title)
    {
        return $this->getExerciseDao()->findByLikeTitle($title);
    }

    /**
     * @return ExerciseDao
     */
    protected function getExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseDao');
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ExerciseModuleDao
     */
    protected function getItemBankExerciseModuleDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseModuleDao');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
