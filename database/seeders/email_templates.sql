# ************************************************************
# Sequel Ace SQL dump
# Version 20064
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: localhost (MySQL 8.0.33)
# Database: wahlocal
# Generation Time: 2024-04-04 14:11:37 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table email_templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `email_templates`;

CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Wild at Heart',
  `from_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_updated_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_templates_name_unique` (`name`),
  UNIQUE KEY `email_templates_slug_unique` (`slug`),
  KEY `email_templates_last_updated_by_foreign` (`last_updated_by`),
  CONSTRAINT `email_templates_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;

INSERT INTO `email_templates` (`id`, `is_active`, `name`, `slug`, `from_name`, `from_email`, `subject`, `body`, `notes`, `created_at`, `updated_at`, `last_updated_by`)
VALUES
	(3,1,'Attendee connected to host','attendee-connected-to-host',NULL,NULL,'We’ve sent your info to the host of [event_title]','[attendee_name],\r\n\r\nThank you for expressing interest in [event_title].\r\n\r\nWell done! Choosing to get your heart back as man is one of the riskiest things you can ever do.\r\n\r\nIt changes...everything.\r\n\r\nWe’ve sent the info you provided to [host_name] and you can expect to hear from them soon.\r\n\r\nIn the coming days, they will share more details about the event schedule and logistics.\r\n\r\nYou can learn more about Fires including a [video and podcast here](https://wildatheart.org/fires).\r\n\r\nWarm regards,\r\nWild at Heart','Triggered by the AttendeeObserver on creation\r\n\r\nAvailable placeholder variables\r\n\r\n- [attendee_name]\r\n- [event_title]\r\n- [host_name]','2024-04-03 10:47:00','2024-04-04 11:05:26',2),
	(4,1,'Attendee registered','attendee-registered',NULL,NULL,'[attendee_name] has expressed interest in your event.','[host_name],\r\n\r\nWe’ve got exciting news — [attendee_fullname] has expressed interest in your Fire!\r\n\r\nHere’s the scoop:\r\n\r\n- [attendee_fullname]\r\n- [attendee_email]\r\n- [attendee_phone]\r\n- [attendee_city], [attendee_state]\r\n\r\n\r\n[attendee_fullname] is expecting to hear from you soon, so be sure to reach out.\r\n\r\nIt\'s working. Your choice to risk hosting a Fire is already giving others hope.\r\n\r\nPlease don\'t hesitate to reach out if you have any questions or need assistance. We\'re honored to be alongside you in this mission.\r\n\r\nFor the Kingdom,\r\n\r\nWild at Heart','This is triggered from the AttendeeObserver which listens for when an Attendee is created. \r\n\r\nIt has the following placeholder variables available:\r\n\r\n- [host_name]\r\n- [attendee_fullname]\r\n- [attendee_email]\r\n- [attendee_phone]\r\n- [attendee_city]\r\n- [attendee_state]','2024-04-03 10:49:51','2024-04-04 10:51:42',2),
	(5,1,'Event approved','event-approved',NULL,NULL,'Your Event is live!','[event_host_name]\r\n\r\nIt’s official—your Fire is approved. Welcome to the revolution!\r\n\r\nHere are a few suggestions:\r\n\r\n**Share your fire.** Here’s your unique link to share with anyone who may be interested: [Fire Link]\r\n\r\n**Respond in a timely manner.** \r\n\r\nYou\'ll receive an email every time someone expresses interest in your event. It’s up to you to communicate all event details (date/time, location, etc). Please respond promptly as they’ll be waiting to hear from you.\r\n\r\n**Keep an eye on your mailbox.** \r\n\r\nWe’ll be sending you a Firestarter Kit to assist you in organizing the event.\r\n\r\nMen were never meant to bear the weight of their pain or their glory alone. So we lit a Fire. And now you are too!\r\n\r\nFires are a place men find themselves outrageously loved by God, and other men.\r\n\r\nWe are excited to have you on board and can\'t wait to see what God does with your Fire.\r\n\r\nIf you have any questions or require any assistance, please don\'t hesitate to reach out to us at [email_address]\r\n\r\nFor the Kingdom,\r\nJohn and Morgan','This is triggered from the ApproveEvent job which happens inside the VerifyEmailController and CreateEventController depending on the user profile.\r\n\r\nIt has the following placeholder variables available:\r\n\r\n- [event_host_name]\r\n- [Fire Link]\r\n- [email_address]','2024-04-03 10:52:08','2024-04-04 10:49:41',2),
	(6,1,'GIft requested','gift-requested',NULL,NULL,'📦 Your Gift Is Coming!','[event_host_name],\r\n\r\n**Keep an eye on your mailbox.** We’ll be sending you a Firestarter Kit to assist you in organizing the event.\r\n\r\nWe are excited to have you on board and can\'t wait to see what God does with your Fire.\r\n\r\nIf you have any questions or require any assistance, please don\'t hesitate to reach out to us at [email_address].\r\n\r\nFor the Kingdom,\r\n\r\nJohn and Morgan','This is triggered within the HandleSendGiftController and has the following placeholder variables available:\r\n\r\n- [event_host_name]\r\n- [email_address]','2024-04-03 10:53:15','2024-04-04 10:47:32',2),
	(9,1,'Send verification email','send-verification-email',NULL,NULL,'Please verify your new Fire','Great! One last step to complete your registration.\r\n\r\nClick on this link and let\'s light your Fire.\r\n\r\n[call_to_action_link] \r\n\r\nIf you did not create an account or do not recognize this email, please disregard it, and no further action is required.','This is triggered by the CreateEventController and ResendConfirmationEmail action.\r\n\r\nIt has the following placeholder variables:\r\n\r\n- [call_to_action_link]','2024-04-03 10:57:00','2024-04-04 10:45:01',2),
	(11,1,'Send welcome email','send-welcome-email',NULL,NULL,'Your Event is live!','[event_host_name],\r\n\r\nIt’s official—your Fire is live. Welcome to the revolution!\r\n\r\nHere are a few suggestions:\r\n\r\n**Share your Fire.** Here’s your unique link to share with anyone who may be interested: [Fire Link]([event_url])\r\n\r\n**Respond in a timely manner.** You\'ll receive an email every time someone expresses interest in your event. It’s up to you to communicate all event details (date/time, location, etc). Please respond promptly as they’ll be waiting to hear from you.\r\n\r\n**Keep an eye on your mailbox.** We’ll be sending you a Firestarter Kit to assist you in organizing the event.\r\n\r\nMen were never meant to bear the weight of their pain or their glory alone. So we lit a Fire. And now you are too!\r\n\r\nFires are a place men find themselves outrageously loved by God, and other men.\r\n\r\nWe are excited to have you on board and can\'t wait to see what God does with your Fire.\r\n\r\nIf you have any questions or require any assistance, please don\'t hesitate to reach out to us at [email_address]\r\n\r\nFor the Kingdom,\r\n\r\nJohn and Morgan','This is triggered from the CreateEventController and should has the following variable placeholders available\r\n\r\n- [event_host_name]\r\n- [event_url]\r\n- [email_address]','2024-04-03 10:59:08','2024-04-04 10:43:15',2);

/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
