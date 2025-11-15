-- PT Phase 1 Backfill Helper
-- ------------------------------------------------------------
-- IMPORTANT:
-- 1. Take a full database backup before running any statement.
-- 2. Review each query against a staging copy first.
-- 3. Adjust casting/branch defaults as needed for your environment.

START TRANSACTION;

/* 1) Mirror legacy subscription trainer assignments into the new matrix. */
INSERT INTO sw_gym_pt_class_trainers (
    branch_setting_id,
    class_id,
    trainer_id,
    session_type,
    session_count,
    commission_rate,
    is_active,
    created_at,
    updated_at
)
SELECT
    COALESCE(st.branch_setting_id, c.branch_setting_id),
    st.pt_class_id,
    st.pt_trainer_id,
    NULL,
    COALESCE(NULLIF(st.num_subscriptions, 0), CAST(NULLIF(c.classes, '') AS UNSIGNED), 0),
    0,
    IFNULL(1 - st.is_completed, 1),
    COALESCE(st.created_at, NOW()),
    COALESCE(st.updated_at, NOW())
FROM sw_gym_pt_subscription_trainer st
INNER JOIN sw_gym_pt_classes c ON c.id = st.pt_class_id
WHERE st.deleted_at IS NULL
  AND st.pt_class_id IS NOT NULL
  AND NOT EXISTS (
        SELECT 1
        FROM sw_gym_pt_class_trainers ct
        WHERE ct.class_id = st.pt_class_id
          AND ct.trainer_id = st.pt_trainer_id
          AND ct.deleted_at IS NULL
  );

/* 2) Normalise PT class metadata for the new schema. */
UPDATE sw_gym_pt_classes c
LEFT JOIN (
    SELECT pt_class_id AS class_id, COUNT(DISTINCT pt_trainer_id) AS trainer_count
    FROM sw_gym_pt_subscription_trainer
    WHERE deleted_at IS NULL AND pt_class_id IS NOT NULL
    GROUP BY pt_class_id
) trainer_stats ON trainer_stats.class_id = c.id
SET
    c.total_sessions = CASE
        WHEN (c.total_sessions IS NULL OR c.total_sessions = 0)
        THEN COALESCE(CAST(NULLIF(c.classes, '') AS UNSIGNED), c.total_sessions, 0)
        ELSE c.total_sessions
    END,
    c.max_members = COALESCE(c.max_members, c.member_limit),
    c.schedule = COALESCE(c.schedule, c.reservation_details),
    c.pricing_type = COALESCE(c.pricing_type, 'per_member'),
    c.class_type = CASE
        WHEN trainer_stats.trainer_count > 1 THEN 'mixed'
        WHEN COALESCE(c.member_limit, 0) > 1 THEN 'group'
        ELSE COALESCE(c.class_type, 'private')
    END,
    c.is_mixed = CASE
        WHEN trainer_stats.trainer_count > 1 THEN 1
        ELSE COALESCE(c.is_mixed, 0)
    END,
    c.is_active = COALESCE(c.is_active, 1),
    c.updated_at = NOW()
WHERE c.deleted_at IS NULL;

/* 3) Align member records with the new session-based fields. */
UPDATE sw_gym_pt_members m
LEFT JOIN sw_gym_pt_classes c ON c.id = m.pt_class_id
LEFT JOIN sw_gym_pt_class_trainers ct
       ON ct.class_id = COALESCE(m.class_id, m.pt_class_id)
      AND ct.trainer_id = m.pt_trainer_id
      AND ct.deleted_at IS NULL
SET
    m.class_id = COALESCE(m.class_id, m.pt_class_id),
    m.class_trainer_id = COALESCE(m.class_trainer_id, ct.id),
    m.total_sessions = CASE
        WHEN (m.total_sessions IS NULL OR m.total_sessions = 0)
        THEN COALESCE(CAST(NULLIF(m.classes, '') AS UNSIGNED), c.total_sessions, ct.session_count, 0)
        ELSE m.total_sessions
    END,
    m.remaining_sessions = CASE
        WHEN (m.remaining_sessions IS NULL OR m.remaining_sessions = 0)
        THEN GREATEST(
            COALESCE(CAST(NULLIF(m.classes, '') AS UNSIGNED), c.total_sessions, ct.session_count, 0)
            - COALESCE(m.visits, 0),
            0
        )
        ELSE m.remaining_sessions
    END,
    m.paid_amount = CASE
        WHEN m.paid_amount IS NULL OR m.paid_amount = 0
        THEN COALESCE(m.amount_paid, 0)
        ELSE m.paid_amount
    END,
    m.discount = CASE
        WHEN m.discount IS NULL OR m.discount = 0
        THEN COALESCE(m.discount_value, 0)
        ELSE m.discount
    END,
    m.start_date = COALESCE(m.start_date, m.joining_date),
    m.end_date = COALESCE(m.end_date, m.expire_date),
    m.is_active = CASE
        WHEN COALESCE(m.end_date, m.expire_date) IS NOT NULL
             AND COALESCE(m.end_date, m.expire_date) < CURDATE() THEN 0
        WHEN GREATEST(
                 COALESCE(CAST(NULLIF(m.classes, '') AS UNSIGNED), c.total_sessions, ct.session_count, 0)
                 - COALESCE(m.visits, 0),
                 0
             ) <= 0 THEN 0
        ELSE 1
    END,
    m.updated_at = NOW()
WHERE m.deleted_at IS NULL;

/* 4) Populate attendee branch references for consistency. */
UPDATE sw_gym_pt_member_attendees a
INNER JOIN sw_gym_pt_members m ON m.id = a.pt_member_id
SET a.branch_setting_id = COALESCE(a.branch_setting_id, m.branch_setting_id),
    a.updated_at = NOW()
WHERE a.deleted_at IS NULL
  AND (a.branch_setting_id IS NULL OR a.branch_setting_id = 0);

COMMIT;

