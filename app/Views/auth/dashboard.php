<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold">Dashboard</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- ✅ Welcome Card -->
    <div class="card shadow-lg mx-auto mb-4" style="max-width: 700px; border-radius: 15px;">
        <div class="card-body text-center p-5">
            <h3 class="card-title mb-3">
                Welcome, <span class="text-primary text-capitalize"><?= esc($user_name) ?></span> 🎉
            </h3>
            <p class="text-muted">
                Role: <strong class="text-capitalize"><?= esc($user_role) ?></strong>
            </p>
        </div>
    </div>

    <?php if ($user_role === 'student'): ?>
        <!-- ✅ Enrolled Courses -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white fw-bold">My Enrolled Courses</div>
            <ul class="list-group list-group-flush" id="enrolledCourses">
                <?php if (!empty($enrolledCourses)): ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <li class="list-group-item"><?= esc($course['title']) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted no-enrollment-msg">
                        You are not enrolled in any course yet.
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ✅ Available Courses -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white fw-bold">Available Courses</div>
            <ul class="list-group list-group-flush">
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="course-title"><?= esc($course['title']) ?></span>
                            <button 
                                class="btn btn-sm btn-success enroll-btn" 
                                data-course-id="<?= $course['id'] ?>">
                                Enroll
                            </button>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted">No available courses.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ✅ Alert Box for AJAX feedback -->
        <div id="alertBox" class="alert mt-3 d-none"></div>
    <?php endif; ?>
</div>

<!-- ✅ AJAX Enrollment Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('.enroll-btn').click(function() {
        const courseId = $(this).data('course-id');
        const button = $(this);
        const courseTitle = button.closest('li').find('.course-title').text().trim();

        $.ajax({
            url: "<?= base_url('course/enroll') ?>",
            type: "POST",
            data: {
                course_id: courseId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                const alertBox = $('#alertBox');
                if (response.status === 'success') {
                    alertBox
                        .removeClass('d-none alert-danger')
                        .addClass('alert alert-success')
                        .text(response.message);

                    button.prop('disabled', true).text('Enrolled');
                    $('.no-enrollment-msg').remove();
                    $('#enrolledCourses').append('<li class="list-group-item">' + courseTitle + '</li>');
                } else {
                    alertBox
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text(response.message);
                }
            },
            error: function(xhr) {
                $('#alertBox')
                    .removeClass('d-none alert-success')
                    .addClass('alert alert-danger')
                    .text('An error occurred. Please try again.');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
