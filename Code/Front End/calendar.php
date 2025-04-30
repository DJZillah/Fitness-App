<?php
namespace Fitify;
include 'MoreDBUtil.php';
session_start();
if (empty($_SESSION)) header("Location: login.php");
include 'header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>

<div class="calendar-container card p-4">
  <div id="workoutCalendar"></div>
</div>

<div id="exerciseModal" class="modal hidden items-center justify-center">
  <div class="modal-content card p-4 w-80">
    <h2 class="text-lg font-semibold mb-4">Schedule Workout</h2>
    <form id="exerciseForm">
      <label for="exerciseSelect" class="block mb-1">Exercise</label>
      <select id="exerciseSelect" name="exercise_id" class="form-select w-full mb-4">
        <option value="" disabled selected>Select an exercise…</option>
      </select>
      <input type="hidden" id="selectedDate">
      <div class="flex justify-end">
  <button type="button" id="deleteBtn" class="btn btn-danger mr-2 hidden">Delete</button>
  <button type="button" id="cancelBtn" class="btn btn-secondary mr-2">Cancel</button>
  <button type="submit" class="btn btn-primary">Save</button>
</div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Modal & form elements
  const modal       = document.getElementById('exerciseModal');
  const form        = document.getElementById('exerciseForm');
  const selectEl    = document.getElementById('exerciseSelect');
  const dateInput   = document.getElementById('selectedDate');
  const cancelBtn   = document.getElementById('cancelBtn');
  const deleteBtn   = document.getElementById('deleteBtn');

  let calendar;
  let selectedEventId = null;

  // 1) Load exercises into dropdown
  fetch('get_exercises.php')
    .then(r => r.json())
    .then(data => {
      selectEl.innerHTML = '<option value="" disabled>Select an exercise…</option>';
      data.forEach(ex => {
        let opt = document.createElement('option');
        opt.value = ex.id;
        opt.text  = ex.name;
        selectEl.appendChild(opt);
      });
    })
    .catch(err => {
      console.error('Exercises load failed:', err);
      selectEl.innerHTML = '<option disabled>Unable to load</option>';
    });

  // 2) Initialize FullCalendar
  calendar = new FullCalendar.Calendar(
    document.getElementById('workoutCalendar'),
    {
      initialView: 'dayGridMonth',
      events: 'get_events.php',       // must include id, title, start, exercise_id
      selectable: true,
      height: 'auto',
      contentHeight: 800,
      validRange: function(nowDate) {
  return {
    start: nowDate // can’t pick any date before today
  };
},
      // Click on empty date new event
      select: function(info) {
        selectedEventId = null;
        dateInput.value = info.startStr;
        selectEl.value = '';
        deleteBtn.classList.add('hidden');
        modal.classList.remove('hidden');
      },

      // Click on existing event edit/delete
      eventClick: function(info) {
        selectedEventId = info.event.id;
        dateInput.value = info.event.startStr;
        selectEl.value = info.event.extendedProps.exercise_id;
        deleteBtn.classList.remove('hidden');
        modal.classList.remove('hidden');
      }
    }
  );
  calendar.render();

  // 3) Cancel button hides modal
  cancelBtn.addEventListener('click', () => {
    modal.classList.add('hidden');
    form.reset();
  });

  // 4) Delete handler
  deleteBtn.addEventListener('click', function() {
    if (!selectedEventId) return;
    if (!confirm('Delete this workout?')) return;

    fetch('delete_event.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ id: selectedEventId })
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        calendar.getEventById(selectedEventId).remove();
        modal.classList.add('hidden');
      } else {
        alert('Delete failed: ' + res.message);
      }
    });
  });

  // 5) Form submit add or update
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    let url = 'add_event.php';
    const payload = {
      exercise_id: selectEl.value,
      date: dateInput.value
    };

    if (selectedEventId) {
      url = 'update_event.php';
      payload.id = selectedEventId;
    }

    fetch(url, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        const title = selectEl.options[selectEl.selectedIndex].text;
        if (selectedEventId) {
          const ev = calendar.getEventById(selectedEventId);
          ev.setStart(payload.date);
          ev.setProp('title', title);
        } else {
          calendar.addEvent({
            id: res.id,
            title,
            start: payload.date,
            extendedProps: { exercise_id: payload.exercise_id }
          });
        }
        modal.classList.add('hidden');
        form.reset();
      } else {
        alert('Save failed: ' + res.message);
      }
    });
  });
});
</script>

<?php include 'footer.php'; ?>
