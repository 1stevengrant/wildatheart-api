public class ContactWebhookHandler {
    private static final String WEBHOOK_URL = 'https://wahfires.ghijk.dev/webhooks/salesforce';
    private static final String SHARED_SECRET = 'xxx'; // Same as Laravel .env

    public static void sendToWebhook(List<Contact> oldRecords, List<Contact> newRecords, String operationType) {
        List<Map<String, Object>> recordsToSend = new List<Map<String, Object>>();

        if (operationType == 'UPDATE') {
            for (Integer i = 0; i < newRecords.size(); i++) {
                recordsToSend.add(toPayload(newRecords[i], oldRecords[i], operationType));
            }
        } else if (operationType == 'INSERT' || operationType == 'UNDELETE') {
            for (Contact c : newRecords) {
                if (String.isBlank(c.Email) && String.isBlank(c.FirstName)) continue;
                recordsToSend.add(toPayload(c, null, operationType));
            }
        } else if (operationType == 'DELETE') {
            for (Contact c : oldRecords) {
                recordsToSend.add(toPayload(null, c, operationType));
            }
        }

        if (!recordsToSend.isEmpty()) {
            sendRecordsToWebhook(JSON.serialize(recordsToSend));
        }
    }

    private static Map<String, Object> toPayload(Contact newRec, Contact oldRec, String op) {
        return new Map<String, Object>{
            'operationType' => op,
            'contactId' => (newRec != null ? newRec.Id : oldRec.Id),
            'newRecord' => newRec != null ? convertRecordToMap(newRec) : null,
            'oldRecord' => oldRec != null ? convertRecordToMap(oldRec) : null
        };
    }

    private static Map<String, Object> convertRecordToMap(Contact record) {
        return new Map<String, Object>{
            'Id' => record.Id,
            'FirstName' => record.FirstName,
            'LastName' => record.LastName,
            'Email' => record.Email,
            'Phone' => record.Phone,
            'AccountId' => record.AccountId,
            'Title' => record.Title,
            'Department' => record.Department,
            'OwnerId' => record.OwnerId,
            'CreatedDate' => record.CreatedDate,
            'LastModifiedDate' => record.LastModifiedDate
        };
    }

    private static String computeHmacSha256(String data, String key) {
        Blob keyBlob = Blob.valueOf(key);
        Blob dataBlob = Blob.valueOf(data);
        Blob mac = Crypto.generateMac('HmacSHA256', dataBlob, keyBlob);
        return EncodingUtil.convertToHex(mac);
    }

    @future(callout=true)
    private static void sendRecordsToWebhook(String jsonData) {
        HttpRequest req = new HttpRequest();
        req.setEndpoint(WEBHOOK_URL);
        req.setMethod('POST');
        req.setHeader('Content-Type', 'application/json');

        String signature = computeHmacSha256(jsonData, SHARED_SECRET);
        req.setHeader('X-SF-Signature', signature);
        req.setBody(jsonData);

        try {
            Http http = new Http();
            HttpResponse res = http.send(req);
            System.debug('✅ Webhook sent: ' + res.getStatusCode() + ' - ' + res.getBody());
        } catch (Exception e) {
            System.debug(LoggingLevel.ERROR, '❌ Webhook call failed: ' + e.getMessage());
        }
    }
}
