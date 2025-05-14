trigger ContactWebhookTrigger on Contact (after insert, after update, after delete, after undelete) {
    if (Trigger.isUpdate) {
        ContactWebhookHandler.sendToWebhook(Trigger.old, Trigger.new, 'UPDATE');
    } else if (Trigger.isInsert) {
        ContactWebhookHandler.sendToWebhook(null, Trigger.new, 'INSERT');
    } else if (Trigger.isDelete) {
        ContactWebhookHandler.sendToWebhook(Trigger.old, null, 'DELETE');
    } else if (Trigger.isUndelete) {
        ContactWebhookHandler.sendToWebhook(null, Trigger.new, 'UNDELETE');
    }
}
