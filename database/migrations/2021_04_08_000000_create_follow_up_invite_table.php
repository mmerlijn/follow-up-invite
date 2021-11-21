<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowUpInviteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('fuinvite.tables.fuipatients'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_id')->nullable();
            $table->unsignedInteger('mijnsalt_id')->comment('patient_id in mijnsalt')->nullable();
            $table->string('type', 10)->comment('Onderzoekstype bv fundus');
            $table->date('wait_until')->nullable();
            $table->date('stop')->nullable();
            $table->json('reason')->nullable()->comment('no show, oogarts');
            $table->json('last_test')->nullable()->comment('DRP');
            $table->unsignedSmallInteger('last_visit_location')->nullable();
            $table->date('last_appointment_at')->nullable();
            $table->date('last_test_at')->nullable();
            $table->date('last_invitation_at')->nullable();
            $table->date('last_reminder_invitation_at')->nullable();
            $table->date('next_invitation_at')->nullable()->comment('na deze datum uitnodigen voor onderzoek');
            $table->date('next_test_at')->nullable()->comment('Volgend onderzoek moet dan plaatsvinden');
            $table->unsignedSmallInteger('days_between')->nullable()->comment('Days till next test');
            $table->unsignedTinyInteger('provider')->nullable()->comment('Zorggroep, bv 6=>SEZ. 7=>SAG');
            $table->string('requester', 10)->nullable()->comment('agbcode aanvrager');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create(config('fuinvite.tables.fuidetails'), function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->json('data')->nullable();
            $table->timestamps();
        });
        if (config('database.default') == 'mysql') {
            DB::unprepared("CREATE TRIGGER `fui_patient_after_insert` AFTER INSERT ON `" . config('fuinvite.tables.fuipatients') . "` FOR EACH ROW BEGIN
            INSERT INTO `" . config('fuinvite.tables.fuidetails') . "` (`id`,`data`,`created_at`) VALUES (NEW.id,NULL,NOW()); 
END");
        } else {
            //sqlite
            DB::unprepared("CREATE TRIGGER `fui_patient_after_insert` AFTER INSERT ON `" . config('fuinvite.tables.fuipatients') . "`
            BEGIN
            INSERT INTO `" . config('fuinvite.tables.fuidetails') . "` (`id`,`data`,`created_at`) VALUES (NEW.id,NULL,NOW()); 
END");
        }
        if (config('database.default') == 'mysql') {
            DB::unprepared("CREATE TRIGGER `fui_patient_after_delete` AFTER DELETE ON `" . config('fuinvite.tables.fuipatients') . "` FOR EACH ROW BEGIN
            DELETE FROM `" . config('fuinvite.tables.fuidetails') . "` WHERE id=OLD.id; 
END");
        } else {
            //sqlite
            DB::unprepared("CREATE TRIGGER `fui_patient_after_delete` AFTER DELETE ON `" . config('fuinvite.tables.fuipatients') . "`
            BEGIN
            DELETE FROM `" . config('fuinvite.tables.fuidetails') . "` WHERE id=OLD.id; 
END");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
