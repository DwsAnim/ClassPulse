-- ── Teachers ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS teachers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120)  NOT NULL,
    course      VARCHAR(120)  NOT NULL DEFAULT 'General',
    password    VARCHAR(255)  NOT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_teacher_name (full_name)
);

-- ── Sessions ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sessions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id  INT           NOT NULL,
    title       VARCHAR(200)  NOT NULL,
    room_code   VARCHAR(10)   NOT NULL,
    is_active   TINYINT(1)    DEFAULT 1,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    ended_at    TIMESTAMP     NULL,
    UNIQUE KEY idx_room_code (room_code),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- ── Students ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    session_id  INT           NOT NULL,
    name        VARCHAR(120)  NOT NULL,
    joined_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- ── Questions ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS questions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    session_id   INT          NOT NULL,
    question     TEXT         NOT NULL,
    option_a     TEXT,
    option_b     TEXT,
    option_c     TEXT,
    option_d     TEXT,
    correct      CHAR(1),
    type         ENUM('mcq','true_false','short','math') DEFAULT 'mcq',
    timer        INT          DEFAULT 30,
    sort_order   INT          DEFAULT 0,
    is_launched  TINYINT(1)   DEFAULT 0,
    launched_at  TIMESTAMP    NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- ── Answers ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS answers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    question_id  INT          NOT NULL,
    student_id   INT          NOT NULL,
    answer       VARCHAR(10),
    is_correct   TINYINT(1)   DEFAULT 0,
    time_taken   INT          DEFAULT 0,
    submitted_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)  REFERENCES students(id)  ON DELETE CASCADE
);

-- ── Confusion meter ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS confusion (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    question_id  INT          NOT NULL,
    student_id   INT          NOT NULL,
    feeling      ENUM('got_it','unsure','lost') NOT NULL,
    submitted_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_confusion (question_id, student_id),
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)  REFERENCES students(id)  ON DELETE CASCADE
);