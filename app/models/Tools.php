<?php

requirePHPLib('data');

class Tools {
    private static function lockService() {
        fclose(fopen(UOJContext::documentRoot()."/app/.lock", "a"));
    }

    private static function unlockService() {
        unlink(UOJContext::documentRoot()."/app/.lock");
    }
	
    private static function moveBack($id) {
        $oldID = $id;
        $newID = $id + 1;

        DB::update("update best_ac_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update click_zans set target_id = '{$newID}' where target_id = '{$oldID}' and type='P'");
        DB::update("update contests_problems set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update contests_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update custom_test_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update hacks set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_auth set pid = '{$newID}' where pid = '{$oldID}'");
        DB::update("update problems_contents set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_permissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems_tags set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");

        rename("/var/uoj_data/upload/{$oldID}", "/var/uoj_data/upload/{$newID}");
        rename("/var/uoj_data/{$oldID}", "/var/uoj_data/{$newID}");
        rename("/var/uoj_data/{$oldID}.zip", "/var/uoj_data/{$newID}.zip");
    }

    private static function moveFront($id) {
        $oldID = $id;
        $newID = $id - 1;

        DB::update("update best_ac_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update click_zans set target_id = '{$newID}' where target_id = '{$oldID}' and type='P'");
        DB::update("update contests_problems set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update contests_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update custom_test_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update hacks set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_auth set pid = '{$newID}' where pid = '{$oldID}'");
        DB::update("update problems_contents set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_permissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems_tags set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");

        rename("/var/uoj_data/upload/{$oldID}", "/var/uoj_data/upload/{$newID}");
        rename("/var/uoj_data/{$oldID}", "/var/uoj_data/{$newID}");
        rename("/var/uoj_data/{$oldID}.zip", "/var/uoj_data/{$newID}.zip");
    }

    private static function deleteProblem($id) {
        dataClearProblemData($id);
        DB::query("delete from problems where id ='{$id}'");
        DB::query("delete from problems_contents where id ='{$id}'");
        DB::query("delete from best_ac_submissions where problem_id = '{$id}'");
        DB::query("delete from click_zans set target_id = '{$newID}' where target_id = '{$id}' and type='P'");
        DB::query("delete from contests_problems where problem_id = '{$id}'");
        DB::query("delete from contests_submissions where problem_id = '{$id}'");
        DB::query("delete from custom_test_submissions where problem_id = '{$id}'");
        DB::query("delete from hacks where problem_id = '{$id}'");
        DB::query("delete from problems where id = '{$id}'");
        DB::query("delete from problems_auth where pid = '{$id}'");
        DB::query("delete from problems_contents where id = '{$id}'");
        DB::query("delete from problems_permissions where problem_id = '{$id}'");
        DB::query("delete from problems_tags where problem_id = '{$id}'");
        DB::query("delete from submissions where problem_id = '{$id}'");
    }

    private static function newProblem($id) {
        DB::query("insert into problems (id, title, is_hidden, submission_requirement) values ('{$id}', 'New Problem', 1, '{}')");
        DB::query("insert into problems_contents (id, statement, statement_md) values ($id, '', '')");
        dataNewProblem($id);
    }

    public static function insert($target) {
        Tools::lockService();

        $oldCnt = DB::num_rows("select id from problems");
        $newCnt = $oldCnt + 1;
        $AI = $newCnt + 1;

        print("Total problem {$oldCnt}\n");
        
        for ($id=$oldCnt; $id>=$target; $id--){
            $oldID = $id;
            $newID = $id + 1;
            print("Move problem {$oldID} to {$newID}\n");
            Tools::moveBack($id);
        }

        print("Insert problem {$target}\n");	
        Tools::newProblem($target);
        DB::update("alter table problems AUTO_INCREMENT={$AI}");

        Tools::unlockService();
    }

    public static function delete($target) {
        Tools::lockService();

        $oldCnt = DB::num_rows("select id from problems");
        $newCnt = $oldCnt - 1;
        $AI = $newCnt + 1;

        print("Total problem {$oldCnt}\n");

        print("Delete problem {$target}\n");	
        Tools::deleteProblem($target);

        for ($id=$target+1; $id<=$oldCnt; $id++){
            $oldID = $id;
            $newID = $id - 1;
            print("Move problem {$oldID} to {$newID}\n");
            Tools::moveFront($id);
        }

        DB::update("alter table problems AUTO_INCREMENT={$AI}");

        Tools::unlockService();
    }
}