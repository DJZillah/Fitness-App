<?php
namespace Fitify;
include_once __DIR__ . '/../Back End/MoreDBUtil.php';

    class BadgeTrack 
    {
        private $userID;
        private $conn;
        public function __construct($userID, $conn)
        {
            $this->userID = $userID;
            $this->conn = $conn;
        }

        public function BMICheck() 
        {
            $BMISql = "SELECT category FROM bmi_records WHERE user_id = $this->userID ORDER BY created_at DESC LIMIT 1";
            $result = mysqli_query($this->conn, $BMISql);
            //remember to use this-> for the instance variables

            if ($row = mysqli_fetch_assoc($result)) 
            {
                if ($row['category'] === "Normal weight") 
                {
                    return true;
                }
                else 
                {
                    return false;
                }
            } //if
        } //BMI check end 

        public function ConsumeCheck()
        {
            $calBadsql = "SELECT SUM(TotalCal) AS total FROM Simple_Cal_Log WHERE user_id = $this->userID";
            $result = mysqli_query($this->conn, $calBadsql);


            if ($row = mysqli_fetch_assoc($result)) 
            {
                $bigTotal = $row['total'];
                if ($bigTotal >= 75000) 
                {
                    return true;
                }
                else 
                {
                    return false;
                }
            }
            else //if no data
            {
                return false;
            }
        } //ConsumeCheck end
        


    } //class end

?>