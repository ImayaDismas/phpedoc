<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/26/17
 * Time: 5:14 PM
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `proffesionals` table method ------------------ */

    /**
     * Creating new proffesional
     * @param String $first_name proffesional first name
     * @param String $las_name proffesional last name
     * @param String $email proffesional login email id
     * @param String $password proffesional login password
     */
    public function createProffesional($first_name, $last_name, $email, $password) {
        require_once 'PassHash.php';

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $passwd = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO proffesionals(first_name, last_name, email, passwd, api_key, status) values(?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $passwd, $api_key);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
    }

    /**
     * Checking proffesional login
     * @param String $email proffesional login email id
     * @param String $password proffesional login password
     * @return boolean proffesional login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT passwd FROM proffesionals WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($passwd);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($passwd, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Change proffesional password
     * @param String $password proffesional login password
     */
    public function changeProffesionalPassword($password, $proff_id) {
        require_once 'PassHash.php';

        // Generating password hash
        $passwd = PassHash::hash($password);

        // Generating API key
        $api_key = $this->generateApiKey();

        // insert query
        $stmt = $this->conn->prepare("UPDATE proffesionals set passwd = ?, api_key = ? WHERE proff_id = ?");
        $stmt->bind_param("ssi", $passwd, $api_key, $proff_id);

        $result = $stmt->execute();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // User successfully inserted
            return USER_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create user
            return USER_CREATE_FAILED;
        }
    }

    /**
     * Checking for duplicate proffesional by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT proff_id from proffesionals WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching proffesional by email
     * @param String $email proffesional email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT proff_id, proff_name, api_key, email, cell_no, national_id, location, availability_status, image, first_name, last_name, gender, status, created_at FROM proffesionals WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $proffesional = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proffesional;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching proffesional api key
     * @param String $proff_id proffesional id primary key in proffesionals table
     */
    public function getApiKeyById($proff_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM proffesionals WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $api_key = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching proffesional id by api key
     * @param String $api_key proffesional api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT proff_id FROM proffesionals WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $proff_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proff_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating proffesional api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key proffesional api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT proff_id from proffesionals WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /* ------------- `proffesionals` table method ------------------ */

    /**
     * Fetching proffessional details
     * @param String $proff_id of the proffesional
     */
    public function getProffesional($proff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM proffesionals WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        if ($stmt->execute()) {
            $proffesional = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $proffesional;
        } else {
            return NULL;
        }
    }
    /**
     * Fetching all proffesionals
     * @param String $proff_id of the proffesional
     */
    public function getAllproffesionals() {
        $stmt = $this->conn->prepare("SELECT * FROM proffesionals");
        $stmt->execute();
        $proffesionals = $stmt->get_result();
        $stmt->close();
        return $proffesionals;
    }

    /**
     * Updating proffesional
     * @param String $proff_id id of the proffesional
     * @param String proff_name text
     * @param String $cell_no text
     * @param String $national_id text
     * @param String $location text
     * @param String $image text
     * @param String $first_name text
     * @param String $last_name text
     */
    public function updateProffesional($proff_name, $cell_no, $national_id, $location, $image, $first_name, $last_name, $gender, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesionals set proff_name = ?, cell_no = ?, national_id = ?, location = ?, image = ?, first_name = ?, last_name = ?, gender = ? WHERE proff_id = ?");
        $stmt->bind_param("ssssssssi", $proff_name, $cell_no, $national_id, $location, $image, $first_name, $last_name, $gender, $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Deactivate proffesional account
     * @param String $proff_id id of the proffesional
     * @param String status number
     */
    public function deactivate_activateProffesional($status, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesionals set status = ? WHERE proff_id = ?");
        $stmt->bind_param("s", $status, $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Change proffesional availability
     * @param String $proff_id id of the proffesional
     * @param String availability_status number
     */
    public function changeProffesionalAvailability($availability_status, $proff_id) {
        $stmt = $this->conn->prepare("UPDATE proffesionals set availability_status = ? WHERE proff_id = ?");
        $stmt->bind_param("s", $availability_status, $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Deleting a proffesional
     * @param String $proff_id id of the proffesional to delete
     */
    public function deleteProffesional($proff_id) {
        $stmt = $this->conn->prepare("DELETE * FROM proffesionals WHERE proff_id = ?");
        $stmt->bind_param("i", $proff_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /** ------------- `user_tasks` table method ------------------ */

    /**
     * Function to assign a task to user
     * @param String $user_id id of the user
     * @param String $task_id id of the task
     */
    public function createUserTask($user_id, $task_id) {
        $stmt = $this->conn->prepare("INSERT INTO user_tasks(user_id, task_id) values(?, ?)");
        $stmt->bind_param("ii", $user_id, $task_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}

?>