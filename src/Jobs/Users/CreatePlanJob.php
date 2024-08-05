<?php

namespace Common\Jobs\Users;

use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Jobs\Base\Job;
use Common\Models\Users\User;
use Exception;
use Throwable;

class CreatePlanJob extends Job
{
    /**
     * @param $job
     * @param $data
     *
     * @throws Throwable
     */
    public function fire($job, $data)
    {
        [$userId, $date] = $data;

        try {
            $user = User::find($userId);
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $date);

            if ($user && $date ) {
                $user->createPlan($date);
            }

            $job->delete();
        } catch (Exception $e) {
            LoggerHelper::getLogger()->error($e);
            $job->fail($e);
        }
    }
}
