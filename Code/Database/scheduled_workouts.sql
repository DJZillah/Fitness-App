CREATE TABLE scheduled_workouts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  exercise_id INT NOT NULL,
  scheduled_date DATE NOT NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  notes VARCHAR(255) NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (exercise_id) REFERENCES exercises(id)
);
