--
-- create stored procedure to insert data into `trash`
--
DELIMITER $$
CREATE PROCEDURE log_deleted_record(
    IN p_table_name VARCHAR(255),
    IN p_deleted_data JSON
)
BEGIN
    INSERT INTO `trash` (
        table_name,
        deleted_data,
        deleted_at
    ) VALUES (
        p_table_name,
        p_deleted_data,
        NOW()
    );
END$$
DELIMITER ;

--
-- create trigger for collecting data from two tables (users, movies)
--

--
-- `users`
--
DELIMITER $
CREATE TRIGGER before_users_delete
BEFORE DELETE ON `users`
FOR EACH ROW
BEGIN
    SET @json_data = JSON_OBJECT(
        'id', OLD.id,
        'fullname', OLD.fullname,
        'email', OLD.email,
        'password', OLD.password,
        `phone`, OLD.phone,
        `role`, OLD.role,
        `is_active`, OLD.is_active,
        'created_at', OLD.created_at
    );
    
    CALL log_deleted_record('users', @json_data);
END$
DELIMITER ;

--
-- `movies`
--
DELIMITER $
CREATE TRIGGER before_movies_delete
BEFORE DELETE ON `movies`
FOR EACH ROW
BEGIN
    -- Convert the deleted row to JSON
    SET @json_data = JSON_OBJECT(
        'id', OLD.id,
        'title', OLD.title,
        'poster', OLD.poster,
        'duration', OLD.duration,
        `synopsis`, OLD.synopsis,
        `status`, OLD.status,
        'release_date', OLD.release_date
    );
    
    -- Call the logging procedure
    CALL log_deleted_record('movies', @json_data);
END$
DELIMITER ;


