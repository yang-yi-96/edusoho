<?php
namespace Custom\Service\Homework\Dao;

interface HomeworkResultDao
{
    /**
     * 查找所有可以被userId互评作业答卷，其中包括未达到最小互评数量的，且userId未曾互评过的答卷.
     * @param homework 作业对象.
     * @param userId 参与互评的学员id.
     * @return 可以被互评的作业答卷id集合,.
    **/
    public function findPairReviewableIds($homework,$userId);
}