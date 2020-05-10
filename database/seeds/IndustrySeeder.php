<?php

use App\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $industries = array(
            '商贸' => array(
                '批发',
                '零售',
                '商业综合体管理',
                '市场管理',
            ),
            '家庭服务' => array(
                '家庭服务',
            ),
            '住宿' => array(
                '住宿',
            ),
            '餐饮' => array(
                '餐饮',
            ),
            '文化体育' => array(
                '文化体育',
            ),
            '交通运输' => array(
                '交通运输',
            ),
            '物流配送' => array(
                '仓储',
                '邮政',
            ),

            '会议展览' => array(
                '会议展览',
            ),
            '教育培训' => array(
                '教育培训',
            ),

        );

        $this->command->info('插入Industry.');
        foreach ($industries as $major => $value) {
            $this->command->info('插入' . $major . '\n');
            $bar = $this->command->getOutput()->createProgressBar(count($value));
            $parent = Industry::create(['name'=>$major]);
            foreach ($value as $IndustryName) {
                Industry::create(['pid' => $parent->id, 'name' => $IndustryName]);
                $bar->advance();
            }
            $bar->finish();
        }
    }
}
