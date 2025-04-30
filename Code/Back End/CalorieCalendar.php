<?php
namespace Fitify;
include '../Back End/MoreDBUtil.php';
session_start();
if (empty($_SESSION)) header("Location: login.php");
include '../Front End/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>

<div class="calendar-container card p-4">
  <h2 class="text-xl font-bold mb-4">Your Calorie Log</h2>
  <div id="calorieCalendar"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const calendarEl = document.getElementById('calorieCalendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    contentHeight: 800,
    events: 'get_calorie_events.php', // AJAX JSON source
    eventDidMount: function(info) {
      info.el.style.backgroundColor = '#4caf50';
      info.el.style.border = 'none';
      info.el.style.color = 'white';
    }
  });

  calendar.render();
});
</script>
        <div class="text-center mt-6 mb-4">
            <a href="CalorieCounter.php" class="btn btn-primary">Log More Calories</a>
        </div>

<?php include '../Front End/footer.php'; ?>
