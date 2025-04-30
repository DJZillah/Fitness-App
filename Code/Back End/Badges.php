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

        public function GetWeight() //too complex for one function, get weight first then parse it. 
        {
            $lossSql = "SELECT weight FROM weight_log WHERE user_id = $this->userID ORDER BY created_at ASC";
            $result = mysqli_query($this->conn, $lossSql);
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

            $weights = array_column($rows, 'weight');
            return $weights;
        }

        public function Loss($allWeights) 
        {
            $oldestWeight = $allWeights[0]; //first entry is oldest
            $newestWeight = $allWeights[count($allWeights) - 1];
            if (count($allWeights) <= 1) //at least 2 weights must be logged 
            {
                return false;
            }
            else 
            {
                $difference = $oldestWeight - $newestWeight;
                if ($difference >= 20) 
                {
                    return true; //lost weight
                }
                else 
                {
                    return false; 
                }
            }
        } //loss end 

        public function Gain($allWeights) 
        {
            $oldestWeight = $allWeights[0]; //first entry in DESC is oldest
            $newestWeight = $allWeights[count($allWeights) - 1];
            if (count($allWeights) <= 1) //at least 2 weights must be logged 
            {
                echo "nuh uh";
                return false;
            }
            else 
            {
                $difference = $oldestWeight - $newestWeight;
                if ($difference <= 20) 
                {
                    return true; //gained weight
                }
                else 
                {
                    return false; 
                }
            }
            
        } //gain end 

    } //class end

?>