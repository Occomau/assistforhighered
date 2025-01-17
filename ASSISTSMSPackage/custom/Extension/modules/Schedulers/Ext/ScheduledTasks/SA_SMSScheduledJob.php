<?php

require_once 'custom/include/Services/SA_SMS/SA_SMSClient.php';
class SA_SMSSingleJob implements RunnableSchedulerJob{
    public function run($smsBeanId)
    {
        global $timedate;
        $smsBean = BeanFactory::getBean('SA_SMS', $smsBeanId);
        if(empty($smsBean->id)){
            return false;
        }
        $client = SA_SMSClient::getClientFromConfig();
        if(!$client){
            return false;
        }
        $beans = [
            $smsBean->parent_type => $smsBean->parent_id
        ];
        $messageContents = SA_SMSClient::parseBody($smsBean->description, $beans);
        $res = $client->sendSMS($smsBean->to_number,$messageContents);
        $smsBean->is_scheduled = false;
        $smsBean->date_sent = $timedate->nowDb();
        if($res){
            $smsBean->third_party = $res['third_party'];
            $smsBean->third_party_id = $res['third_party_id'];
            $smsBean->status = $res['status'];
        }
        $smsBean->save();
        return true;
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
}
class SA_SMSScheduledJob implements RunnableSchedulerJob
{
    public function run($campaignId)
    {

        $campaign = BeanFactory::getBean('Campaigns', $campaignId);
        if (empty($campaign->id)) {
            return false;
        }
        $this->client = SA_SMSClient::getClientFromConfig();
        return $this->processCampaign($campaign);
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }


    function sendSMSForPerson(Campaign $campaign, $listId, Person $person){
        global $current_user, $timedate;
        $dup_query = "select id from campaign_log where campaign_id = ".$campaign->db->quoted($campaign->id)." and target_id = ".$campaign->db->quoted($person->id);
        $dup_result = $campaign->db->query($dup_query);
        $row = $campaign->db->fetchByAssoc($dup_result);

        if (!empty($row)) {
            //We have already attempted or successfully sent.
            return false;
        }

        $campaign_log = BeanFactory::newBean('CampaignLog');
        $campaign_log->campaign_id = $campaign->id;
        $campaign_log->activity_date = $timedate->now();
        $campaign_log->related_id = '';
        $campaign_log->related_type = '';
        $campaign_log->target_id = $person->id;
        $campaign_log->target_type = $person->module_dir;
        $campaign_log->marketing_id = '';
        $campaign_log->resend_type = '';
        $tracker_id = create_guid();
        $campaign_log->target_tracker_key = $tracker_id;
        $campaign_log->list_id = $listId;
        $campaign_log->activity_type = 'targeted';

        if(!$person->phone_mobile){
            $campaign_log->activity_type = 'send error';
            $campaign_log->save();
            return false;
        }
        if($person->sa_sms_opt_in != 'Y'){
            $campaign_log->activity_type = 'removed';
            $campaign_log->save();
            return false;
        }
        $beans = [
            $person->module_name => $person->id,
            'Campaigns' => $campaign->id,
        ];
        $messageContents = SA_SMSClient::parseBody($campaign->sa_sms_contents, $beans);
        try{
            $res = $this->client->sendSMS($person->phone_mobile, $messageContents);
            $smsBean = BeanFactory::newBean('SA_SMS');
            $smsBean->name = "From: ".$this->client->getFrom()." To: ".$person->phone_mobile;
            $smsBean->description = $campaign->sa_sms_contents;
            $smsBean->from_number = $this->client->getFrom();
            $smsBean->to_number = $person->phone_mobile;
            $smsBean->sms_type = 'crm_out';
            $smsBean->send_record_id = $current_user->id;
            $smsBean->send_record_type = "Users";
            $smsBean->to_record_id = $person->id;
            $smsBean->to_record_type = $person->module_name;
            $smsBean->parent_id = $person->id;
            $smsBean->parent_type = $person->module_name;
            $smsBean->date_sent = $timedate->nowDb();
            $smsBean->is_scheduled = false;
            if($res){
                $smsBean->third_party = $res['third_party'];
                $smsBean->third_party_id = $res['third_party_id'];
                $smsBean->status = $res['status'];
            }
            $smsBean->save();

        }catch(Exception $ex){
            $GLOBALS['log']->warn("Failed to send Campaign SMS ".$ex->getMessage());
            $campaign_log->activity_type = "send error";
        }
        $campaign_log->save();
        return true;
    }

    function processTargetList($campaign, $targetList)
    {
        $targetContactRels = ['contacts' => 'Contacts', 'leads' => 'Leads'];
        foreach ($targetContactRels as $rel => $mod) {
            $targetList->load_relationship($rel);
            $personIds = $targetList->$rel->get();
            foreach ($personIds as $id) {
                $person = BeanFactory::getBean($mod, $id);
                if (empty($person->id)) {
                    continue;
                }
                $this->sendSMSForPerson($campaign, $targetList->id, $person);
            }
        }
    }

    function processCampaign(Campaign $campaign)
    {
        if(!$campaign->sa_sms_contents){
            return false;
        }
        foreach ($campaign->get_linked_beans('prospectlists') as $targetList) {
            if ($targetList->list_type == 'exempt') {
                continue;
            }
            $this->processTargetList($campaign, $targetList);
        }
        return true;
    }
}