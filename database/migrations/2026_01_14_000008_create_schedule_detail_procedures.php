<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_manage_schedule_detail(TEXT, TEXT, TEXT, INT, TEXT, TEXT, TEXT, TEXT);

            CREATE OR REPLACE PROCEDURE sp_manage_schedule_detail(
                p_operation VARCHAR,
                p_id VARCHAR, -- schedule_detail_id (for update/delete)
                p_schedule_id VARCHAR, -- (for create)
                p_sequence INT, -- (for create)
                p_ticket_id VARCHAR,
                p_seat_number VARCHAR, -- New Param
                p_attendance_status VARCHAR,
                p_remarks TEXT
            )
            LANGUAGE plpgsql AS $$
            BEGIN
                IF p_operation = 'CREATE' THEN
                    -- Note: ID generation logic usually in trigger or model. 
                    -- Here we assume caller provides generated ID or DB handles it.
                    -- Recalling model logic: ScheduleDetail has custom ID generation in PHP Model boot().
                    -- For strict SP usage, we might need to duplicate that logic here or pass ID.
                    -- Let's assume we rely on Model for ID if used via Eloquent, 
                    -- BUT if this SP is the *primary* way, we should generate it here.
                    -- However, keeping it simple: Insert into table.
                    
                    INSERT INTO schedule_details (id, schedule_id, sequence, ticket_id, seat_number, attendance_status, remarks, created_at, updated_at)
                    VALUES (p_schedule_id || '-' || p_sequence, p_schedule_id, p_sequence, p_ticket_id, p_seat_number, p_attendance_status, p_remarks, NOW(), NOW());
                    
                ELSIF p_operation = 'UPDATE' THEN
                    UPDATE schedule_details SET 
                        ticket_id = p_ticket_id,
                        seat_number = p_seat_number,
                        attendance_status = p_attendance_status,
                        remarks = p_remarks,
                        updated_at = NOW()
                    WHERE id = p_id;
                    
                ELSIF p_operation = 'DELETE' THEN
                    DELETE FROM schedule_details WHERE id = p_id;
                END IF;
                
                COMMIT;
            END;
            $$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_manage_schedule_detail(TEXT, TEXT, TEXT, INT, TEXT, TEXT, TEXT);
        ");
    }
};
