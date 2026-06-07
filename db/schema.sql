-- ============================================================
--  Chat — schéma de la base de données MySQL
--  Création : mysql -u root -p < db/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS chat
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE chat;

-- Membres (utilisateurs du chat)
CREATE TABLE IF NOT EXISTS member (
    member_id     INT AUTO_INCREMENT PRIMARY KEY,
    member_nom    VARCHAR(100) NOT NULL,
    member_prenom VARCHAR(100) NOT NULL,
    member_email  VARCHAR(190) NOT NULL UNIQUE,
    member_pwd    VARCHAR(255) NOT NULL,
    member_statut VARCHAR(20)  NOT NULL DEFAULT 'déconnecté'
) ENGINE=InnoDB;

-- Messages échangés entre deux membres
CREATE TABLE IF NOT EXISTS messenger (
    messenger_id          INT AUTO_INCREMENT PRIMARY KEY,
    messenger_content     TEXT NOT NULL,
    messenger_id_sender   INT NOT NULL,
    messenger_id_receiver INT NOT NULL,
    messenger_date        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (messenger_id_sender)   REFERENCES member(member_id),
    FOREIGN KEY (messenger_id_receiver) REFERENCES member(member_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempt (
    login_id    INT AUTO_INCREMENT PRIMARY KEY,
    login_email VARCHAR(190) NOT NULL,
    login_ip    VARCHAR(45)  NOT NULL,   -- 45 = compatible IPv6
    login_date  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_date (login_email, login_date)
) ENGINE=InnoDB;
