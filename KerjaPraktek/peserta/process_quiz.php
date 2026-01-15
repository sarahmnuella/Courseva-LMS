<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $quiz_id = (int)$_POST['quiz_id'];
    $course_id = (int)$_POST['course_id'];
    
    // 1. Ambil data kuis untuk mengetahui passing score
    $quiz = executeQuery("SELECT * FROM QUIZ WHERE quiz_id = ?", "i", [$quiz_id])->fetch_assoc();
    
    // 2. Ambil semua pertanyaan kuis tersebut
    $questions_res = executeQuery("SELECT question_id, points FROM QUIZ_QUESTIONS WHERE quiz_id = ?", "i", [$quiz_id]);
    
    $total_score = 0;
    $user_score = 0;
    $correct_answers = 0;
    $total_questions = $questions_res->num_rows;

    while ($q = $questions_res->fetch_assoc()) {
        $q_id = $q['question_id'];
        $total_score += $q['points'];
        
        // Cek jawaban user dari POST
        if (isset($_POST['question_' . $q_id])) {
            $user_ans_id = (int)$_POST['question_' . $q_id];
            
            // Cek ke database apakah jawaban tersebut benar
            $check_ans = executeQuery("SELECT is_correct FROM QUIZ_ANSWERS WHERE answer_id = ? AND question_id = ?", "ii", [$user_ans_id, $q_id])->fetch_assoc();
            
            if ($check_ans && $check_ans['is_correct'] == 1) {
                $user_score += $q['points'];
                $correct_answers++;
            }
        }
    }

    // 3. Hitung Persentase
    $percentage = ($user_score / $total_score) * 100;
    $status = ($percentage >= $quiz['passing_score']) ? 'passed' : 'failed';

    // 4. Simpan ke tabel QUIZ_RESULTS
    $query_result = "INSERT INTO QUIZ_RESULTS (user_id, quiz_id, score, total_score, percentage, status) VALUES (?, ?, ?, ?, ?, ?)";
    $result_id = executeInsert($query_result, "iiiids", [$user_id, $quiz_id, $user_score, $total_score, $percentage, $status]);

    // 5. Update Progress Course jika Lulus
    if ($status == 'passed') {
        executeUpdate("UPDATE USER_COURSE_PROGRESS SET status = 'completed', progress_percentage = 100, completed_at = CURRENT_TIMESTAMP WHERE user_id = ? AND course_id = ?", "ii", [$user_id, $course_id]);
    }

    header("Location: QuizMulai.php?result_id=" . $result_id);
    exit();
}