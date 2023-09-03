<?php
/**
 * Copyright (C) 2009-2012 Peter Lind
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 * PHP version 5
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * stores routes and handles route lookup for request handler and url generation
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Routes
{

    /**
     * the instance of this object
     *
     * @var object
     */
    protected static $instance;

    /**
     * where the routes are stored
     *
     * @var array
     */
    protected $routes = array();

    /**
     * instance of the config object
     *
     * @var Config
     */
    protected $config;

    /**
     * contains matches found for uri's
     *
     * @var array
     */
    protected $matches = array();

    /**
     * public constructor
     *
     * @param Config $config Config object
     *
     * @access public
     * @return void
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->routes['home']                  = array('url' => '', 'controller' => 'Index', 'method' => 'main');
        $this->routes['default']               = array('url' => '*', 'controller' => 'Index', 'method' => 'main');
        $this->routes['no_access']             = array('url' => 'noaccess', 'controller' => 'Index', 'method' => 'noAccess');
        $this->routes['login_page']            = array('url' => 'login', 'controller' => 'Index', 'method' => 'login');
        $this->routes['logout_page']           = array('url' => 'logout', 'controller' => 'Index', 'method' => 'logout');
        $this->routes['forgotten_pass']        = array('url' => 'forgotten-pass', 'controller' => 'Index', 'method' => 'forgottenPassDialog');
        $this->routes['forgotten_pass_submit'] = array('url' => 'forgotten-pass-submit', 'controller' => 'Index', 'method' => 'forgottenPassAction');
        $this->routes['reset_pass']            = array('url' => 'reset-pass/:hash:', 'controller' => 'Index', 'method' => 'resetPassDialog');
        $this->routes['reset_pass_submit']     = array('url' => 'reset-pass-submit/:hash:', 'controller' => 'Index', 'method' => 'resetPassAction');

        // SMS stuffs - hackish
        $this->routes['kickoff_sms_script'] = array('url' => 'index/kickoffsmsscript', 'controller' => 'index', 'method' => 'kickOffSMSScript');
        $this->routes['sms_auto_dryrun']    = array('url' => 'sms/auto-dryrun', 'controller' => 'sms', 'method' => 'autoDryRun');

        // deltager routes
        $this->routes['all_users_ajax']                             = array('url' => 'deltager/ajax/userlist', 'controller' => 'Participant', 'method' => 'ajaxlist');
        $this->routes['ajax_user_search']                           = array('url' => 'deltager/ajaxsearch/:vagt_id:/:term:', 'controller' => 'Participant', 'method' => 'ajaxUserSearch');
        $this->routes['ajax_get_user_types']                        = array('url' => 'deltager/ajax/get_user_types', 'controller' => 'Participant', 'method' => 'ajaxGetUserTypes');
        $this->routes['delete_deltager']                            = array('url' => 'deltager/delete/:id:', 'controller' => 'Participant', 'method' => 'deleteDeltager');
        $this->routes['deltagere_karmalist']                        = array('url' => 'deltager/karmalist', 'controller' => 'Participant', 'method' => 'karmaList');
        $this->routes['deltagere_karmalistsorteret']                = array('url' => 'deltager/karmalist/:direction:', 'controller' => 'Participant', 'method' => 'karmaList');
        $this->routes['deltagerehome']                              = array('url' => 'deltager/', 'controller' => 'Participant', 'method' => 'main');
        $this->routes['edit_deltager']                              = array('url' => 'deltager/retdeltager/:id:', 'controller' => 'Participant', 'method' => 'visEdit');
        $this->routes['edit_deltager_note']                         = array('url' => 'deltager/retdeltagernote/:textfield:/:id:', 'controller' => 'Participant', 'method' => 'visTextedit');
        $this->routes['karma_stats']                                = array('url' => 'deltager/karmastats/', 'controller' => 'Participant', 'method' => 'karmaStatus');
        $this->routes['list_schedule_signups']                      = array('url' => 'deltager/visforafvikling/:afvikling_id:/:assigned:', 'controller' => 'Participant', 'method' => 'listForSchedule');
        $this->routes['list_group_participants']                    = array('url' => 'deltager/visforhold/:hold_id:', 'controller' => 'Participant', 'method' => 'listForGroup');
        $this->routes['opret_deltager']                             = array('url' => 'deltager/opret/', 'controller' => 'Participant', 'method' => 'createDeltager');
        $this->routes['participant_info']                           = array('url' => 'deltager/tilmeldingsinfo/:hash:', 'controller' => 'Participant', 'method' => 'displayParticipantInfo');
        $this->routes['print_list']                                 = array('url' => 'deltager/printlist/', 'controller' => 'Participant', 'method' => 'printList');
        $this->routes['sms_send']                                   = array('url' => 'deltager/sendsmstexts/', 'controller' => 'Participant', 'method' => 'sendSMSes');
        $this->routes['show_d_search']                              = array('url' => 'deltager/showsearch/', 'controller' => 'Participant', 'method' => 'showSearch');
        $this->routes['show_bought_food']                           = array('url' => 'deltager/boughtfood/:type:', 'controller' => 'Participant', 'method' => 'showBoughtFood');
        $this->routes['sms_dialog']                                 = array('url' => 'deltager/smsdialog/', 'controller' => 'Participant', 'method' => 'displaySMSDialog');
        $this->routes['spillersedler']                              = array('url' => 'deltager/spillersedler/:id_range:', 'controller' => 'Participant', 'method' => 'spillerSedler');
        $this->routes['update_deltager']                            = array('url' => 'deltager/update/:id:', 'controller' => 'Participant', 'method' => 'updateDeltager');
        $this->routes['update_deltager_aktiviteter']                = array('url' => 'deltager/updateaktivitet/:id:', 'controller' => 'Participant', 'method' => 'updateAktiviteter');
        $this->routes['update_deltager_aktivitets_tilmeldinger']    = array('url' => 'deltager/updatetilmelding/:id:', 'controller' => 'Participant', 'method' => 'updateTilmeldinger');
        $this->routes['update_deltager_gds']                        = array('url' => 'deltager/updategds/:id:', 'controller' => 'Participant', 'method' => 'updateGDS');
        $this->routes['update_deltager_gdstilmeldinger']            = array('url' => 'deltager/updategdstilmeldinger/:id:', 'controller' => 'Participant', 'method' => 'updateGDSTilmeldinger');
        $this->routes['update_deltager_madwear']                    = array('url' => 'deltager/updatemadwear/:id:', 'controller' => 'Participant', 'method' => 'updateMadWear');
        $this->routes['update_deltager_note']                       = array('url' => 'deltager/updatenote/:textfield:/:id:', 'controller' => 'Participant', 'method' => 'updateDeltagerNote');
        $this->routes['update_participant_sleeping']                = array('url' => 'deltager/updatesleeping/:id:', 'controller' => 'Participant', 'method' => 'updateParticipantSleeping');
        $this->routes['update_participant_sleeping_post']           = array('url' => 'deltager/persistsleepingdata/:id:', 'controller' => 'Participant', 'method' => 'updateParticipantSleepingData');
        $this->routes['vis_alle_deltagere']                         = array('url' => 'deltager/visalle', 'controller' => 'Participant', 'method' => 'visAlle');
        $this->routes['show_search_result']                         = array('url' => 'deltager/search_result', 'controller' => 'Participant', 'method' => 'showSearchResult');
        $this->routes['download_participant_sheet']                 = array('url' => 'deltager/spradsheet', 'controller' => 'Participant', 'method' => 'downloadSpreadSheet');
        $this->routes['vis_spilledere']                             = array('url' => 'deltager/visgms/', 'controller' => 'Participant', 'method' => 'listGMs');
        $this->routes['vis_fordelte_spilledere']                    = array('url' => 'deltager/visfordeltegms/:id:', 'controller' => 'Participant', 'method' => 'listAssignedGMs');
        $this->routes['visdeltager']                                = array('url' => 'deltager/visdeltager/:id:', 'controller' => 'Participant', 'method' => 'visDeltager');
        $this->routes['visprint']                                   = array('url' => 'deltager/showsignup/:id:', 'controller' => 'Participant', 'method' => 'showSignupDetails');
        $this->routes['json_showsignup']                            = array('url' => 'json/showsignup/:id:', 'controller' => 'Participant', 'method' => 'showSignupDetailsJson');
        $this->routes['payment_interface']                          = array('url' => 'deltager/payment', 'controller' => 'Participant', 'method' => 'payment');
        $this->routes['payment_interface_ajax']                     = array('url' => 'deltager/payment/ajax', 'controller' => 'Participant', 'method' => 'paymentAjax');
        $this->routes['checkin_interface']                          = array('url' => 'deltager/checkin', 'controller' => 'Participant', 'method' => 'checkin');
        $this->routes['checkin_interface_ajax']                     = array('url' => 'deltager/checkin/ajax', 'controller' => 'Participant', 'method' => 'checkinAjax');
        $this->routes['edit_participant_types']                     = array('url' => 'deltager/types/edit', 'controller' => 'Participant', 'method' => 'editTypes');
        $this->routes['participant_editable_url']                   = array('url' => 'deltager/ajax/editable/:id:', 'controller' => 'Participant', 'method' => 'doAjaxEdit');
        $this->routes['participant_ajax_search_parameters']         = array('url' => 'deltager/ajax/parametersearch', 'controller' => 'Participant', 'method' => 'ajaxParameterSearch');
        $this->routes['participant_remove_schedule']                = array('url' => 'deltager/ajax/removeschedule', 'controller' => 'Participant', 'method' => 'ajaxRemoveSchedule');
        $this->routes['participant_update_schedule']                = array('url' => 'deltager/ajax/updateschedule', 'controller' => 'Participant', 'method' => 'ajaxUpdateSchedule');
        $this->routes['participant_update_schedule_priorities']     = array('url' => 'deltager/ajax/updateschedulepriorities', 'controller' => 'Participant', 'method' => 'ajaxUpdateSchedulePriorities');
        $this->routes['ean8_barcode']                               = array('url' => 'participant/ean8/:participant_id:', 'controller' => 'Participant', 'method' => 'ean8Barcode');
        $this->routes['ean8_barcode_small']                         = array('url' => 'participant/ean8small/:participant_id:', 'controller' => 'Participant', 'method' => 'ean8SmallBarcode');
        $this->routes['ean8_badge']                                 = array('url' => 'participant/ean8badge/:participant_id:', 'controller' => 'Participant', 'method' => 'ean8Badge');
        $this->routes['ean8_sheet']                                 = array('url' => 'participant/ean8sheet/:participant_id:', 'controller' => 'Participant', 'method' => 'ean8Sheet');
        $this->routes['email_list']                                 = array('url' => 'participant/email-list', 'controller' => 'Participant', 'method' => 'displayEmailList');
        $this->routes['participant_signup_email']                   = array('url' => 'participant/send-signup-email/:id:', 'controller' => 'Participant', 'method' => 'sendSignupEmail');
        $this->routes['participant_check_for_voucher']              = array('url' => 'participant/has-vouchers/:participant_id:', 'controller' => 'Participant', 'method' => 'checkForVouchers');
        $this->routes['show_double_bookings']                       = array('url' => 'participant/check-double-bookings', 'controller' => 'Participant', 'method' => 'checkForDoubleBookings');
        $this->routes['show_refund']                                = array('url' => 'participant/show-refund', 'controller' => 'Participant', 'method' => 'showRefund');
        $this->routes['name_tag_list']                              = array('url' => 'participant/name-tag-list', 'controller' => 'Participant', 'method' => 'nameTagList');
        $this->routes['register_mobilepay_payments']                = array('url' => 'participant/register-mobilepay', 'controller' => 'Participant', 'method' => 'registerMobilepay');
        $this->routes['confirm_mobilepay_payments']                 = array('url' => 'participant/ajax/confirm-mobilepay', 'controller' => 'Participant', 'method' => 'ajaxConfirmPayment');

        $this->routes['participant_reset_password']                 = array('url' => 'participant/reset-password/:hash:', 'controller' => 'Participant', 'method' => 'resetParticipantPassword');

        $this->routes['anonymize_participants']                     = array('url' => 'participant/anonymize', 'controller' => 'Participant', 'method' => 'anonymizeParticipants');

        // New mail sender
        $this->routes['send_setup_mail']                            = array('url' => 'mail/sendsetupmail', 'controller' => 'Mail', 'method' => 'sendSetupMail');
        $this->routes['send_evaluation_mail']                       = array('url' => 'mail/sendevaluationmail', 'controller' => 'Mail', 'method' => 'sendEvaluationMail');
        $this->routes['send_password_mail']                         = array('url' => 'mail/fixpassword', 'controller' => 'Mail', 'method' => 'fixPasswords');
        
        // Misc mail
        $this->routes['send_welcome_mail']                          = array('url' => 'participant/sendwelcomemail', 'controller' => 'Participant', 'method' => 'sendWelcomeMail');
        $this->routes['send_review_mail']                           = array('url' => 'participant/sendreviewmail', 'controller' => 'Participant', 'method' => 'sendReviewMail');

        // photo stuff
        $this->routes['photo_upload_form']                          = ['url' => 'photo/form/:identifier:', 'controller' => 'Photo', 'method' => 'showUploadForm'];
        $this->routes['photo_upload_original']                      = ['url' => 'photo/upload/original/:identifier:', 'controller' => 'Photo', 'method' => 'storeOriginal'];
        $this->routes['photo_upload_cropped']                       = ['url' => 'photo/upload/cropped/:identifier:', 'controller' => 'Photo', 'method' => 'storeCropped'];

        $this->routes['send_photo_upload_reminders']                = ['url' => 'photo/send-reminders', 'controller' => 'Photo', 'method' => 'sendUploadReminders'];
        $this->routes['show_missing_photo']                         = ['url' => 'photo/see-missing-photo', 'controller' => 'Photo', 'method' => 'seeMissingPhotos'];
        $this->routes['photo_download']                             = ['url' => 'photo/download', 'controller' => 'Photo', 'method' => 'downloadPhotos'];

        // id template stuff
        $this->routes['template_editing']                           = ['url' => 'id-templates', 'controller' => 'IdTemplate', 'method' => 'showEdit'];
        $this->routes['create_template']                            = ['url' => 'id-templates/create-template', 'controller' => 'IdTemplate', 'method' => 'createTemplate'];
        $this->routes['delete_template']                            = ['url' => 'id-templates/delete-template/:id:', 'controller' => 'IdTemplate', 'method' => 'deleteTemplate'];
        $this->routes['update_template']                            = ['url' => 'id-templates/update-template/:id:', 'controller' => 'IdTemplate', 'method' => 'updateTemplate'];

        $this->routes['update_category_template']                   = ['url' => 'id-templates/update-category-template/:id:', 'controller' => 'IdTemplate', 'method' => 'updateCategoryTemplate'];

        $this->routes['id_card_render']                             = ['url' => 'id-card/render', 'controller' => 'IdTemplate', 'method' => 'renderIdCards'];

        // online payment
        $this->routes['participant_post_payment']                   = array('url' => 'participant/payment/done', 'controller' => 'Participant', 'method' => 'showPaymentDone');
        $this->routes['participant_payment']                        = array('url' => 'participant/payment/:hash:', 'controller' => 'Participant', 'method' => 'processPayment');
        $this->routes['participant_register_payment']               = array('url' => 'participant/payment/register/:hash:', 'controller' => 'Participant', 'method' => 'registerPayment');

        // payment reminders
        $this->routes['cron_payment_reminder']                      = array('url' => 'participant/payment-reminder/cron', 'controller' => 'Participant', 'method' => 'cronPaymentReminder');
        // $this->routes['13-day_payment_reminder']                    = array('url' => 'participant/payment-reminder/second', 'controller' => 'Participant', 'method' => 'sendSecondPaymentReminder');
        $this->routes['final_payment_reminder']                     = array('url' => 'participant/payment-reminder/final', 'controller' => 'Participant', 'method' => 'sendFinalPaymentReminder');
        // $this->routes['payment_reminder_annulled']                  = array('url' => 'participant/payment-reminder/annulled', 'controller' => 'Participant', 'method' => 'cancelParticipantSignup');

        // bank transfer
        $this->routes['participant_register_bank_payment']          = array('url' => 'participant/register-bank-transfer/:id:', 'controller' => 'Participant', 'method' => 'registerBankTransfer');

        // economy stuffs
        $this->routes['economy_breakdown']    = array('url' => 'economy/breakdown', 'controller' => 'Participant', 'method' => 'economyBreakdown');
        $this->routes['accounting_overview']  = array('url' => 'economy/accounting-overview/', 'controller' => 'Economy', 'method' => 'accountingOverview');

        // TODO remove methods and pages only used by this route
        $this->routes['detailed_budget']      = array('url' => 'economy/detailedbudget/', 'controller' => 'Economy', 'method' => 'detailedBudget');

        // graph routes
        $this->routes['graph_participant_signups']       = array('url' => 'graph/ajax/signups', 'controller' => 'Graph', 'method' => 'ajaxSignups');
        $this->routes['graph_participant_signups_total'] = array('url' => 'graph/ajax/total_signups', 'controller' => 'Graph', 'method' => 'ajaxTotalSignups');
        $this->routes['graph_participant_shares']        = array('url' => 'graph/ajax/shares', 'controller' => 'Graph', 'method' => 'ajaxShares');
        $this->routes['graph_food_shares']               = array('url' => 'graph/ajax/foodshares', 'controller' => 'Graph', 'method' => 'ajaxFoodShares');

        // aktivitet routes
        $this->routes['aktiviteterhome']           = array('url' => 'aktiviteter/', 'controller' => 'Activity', 'method' => 'main');
        $this->routes['vis_alle_aktiviteter']      = array('url' => 'aktiviteter/visalle', 'controller' => 'Activity', 'method' => 'visAlle');
        $this->routes['visaktivitet']              = array('url' => 'aktivitet/vis/:id:', 'controller' => 'Activity', 'method' => 'visAktivitet');
        $this->routes['edit_aktivitet']            = array('url' => 'aktivitet/edit/:id:', 'controller' => 'Activity', 'method' => 'editAktivitet');
        $this->routes['edit_afvikling']            = array('url' => 'aktivitet/editafvikling/:id:', 'controller' => 'Activity', 'method' => 'editAfvikling');
        $this->routes['opret_aktivitet']           = array('url' => 'aktivitet/opret/', 'controller' => 'Activity', 'method' => 'opretAktivitet');
		$this->routes['import_activities']         = array('url' => 'aktivitet/importer_eksporter/', 'controller' => 'Activity', 'method' => 'importExportActivities');
        $this->routes['opret_afvikling']           = array('url' => 'aktivitet/opretafvikling/:aktivitet_id:', 'controller' => 'Activity', 'method' => 'opretAfvikling');
        $this->routes['slet_aktivitet']            = array('url' => 'aktivitet/slet/:id:', 'controller' => 'Activity', 'method' => 'sletAktivitet');
        $this->routes['slet_afvikling']            = array('url' => 'aktivitet/sletafvikling/:id:', 'controller' => 'Activity', 'method' => 'sletAfvikling');
        $this->routes['activities_graphed']        = array('url' => 'aktitiveter/graphactivity/:day:', 'controller' => 'Activity', 'method' => 'gameActivity');
        $this->routes['total_available_playtime']  = array('url' => 'aktitiveter/getplaytime', 'controller' => 'Activity', 'method' => 'getTotalPlaytime');
        $this->routes['ajax_get_afviklinger']      = array('url' => 'aktivitet/getafviklinger/:deltager:/:id:', 'controller' => 'Activity', 'method' => 'ajaxGetAfviklinger');
        $this->routes['ajax_get_hold']             = array('url' => 'aktivitet/gethold/:id:', 'controller' => 'Activity', 'method' => 'ajaxGetHold');
        $this->routes['ajax_get_holdneeds']        = array('url' => 'aktivitet/getholdneeds/:id:', 'controller' => 'Activity', 'method' => 'ajaxGetHoldNeeds');
        $this->routes['ajax_getrandomhold']        = array('url' => 'aktivitet/getrandomhold/:id:/:type:', 'controller' => 'Activity', 'method' => 'ajaxGetRandomHold');
        $this->routes['gamestart_details']         = array('url' => 'aktiviteter/gamestart/:time:', 'controller' => 'Activity', 'method' => 'gameStartDetails');
        $this->routes['ajax_activity_schedules']   = array('url' => 'aktiviteter/ajax/activity-schedules/:id:', 'controller' => 'Activity', 'method' => 'ajaxActivitySchedules');
        $this->routes['gamestart_master']          = array('url' => 'aktiviteter/gamestart/master/:time:', 'controller' => 'Activity', 'method' => 'gameStartMaster');
        $this->routes['gamestart_minion']          = array('url' => 'aktiviteter/gamestart/minion/:id:', 'controller' => 'Activity', 'method' => 'gameStartMinion');
        $this->routes['gamestart_minion_change']   = array('url' => 'aktiviteter/gamestart/minion/:id:/change', 'controller' => 'Activity', 'method' => 'gameStartMinionChange');
        $this->routes['gamestart_ajax_info'  ]     = array('url' => 'aktiviteter/gamestart/ajax/:id:/info', 'controller' => 'Activity', 'method' => 'gameStartAjaxInfo');
        $this->routes['gamestart_master_change']   = array('url' => 'aktiviteter/gamestart/master/:id:/change', 'controller' => 'Activity', 'method' => 'gameStartMasterChange');

        $this->routes['priority_signup_statistics'] = ['url' => 'activities/priority-signups', 'controller' => 'Activity', 'method' => 'showPrioritySignupStatistics'];

        $this->routes['prepare_schedule_votes']    = array('url' => 'aktiviteter/schedule-votes/prepare/:time:', 'controller' => 'Activity', 'method' => 'prepareScheduleVotes');
        $this->routes['show_vote_stats']           = array('url' => 'aktiviteter/voting/stats', 'controller' => 'Activity', 'method' => 'showVotingStats');

        $this->routes['activity_vote']             = array('url' => 'vote', 'controller' => 'Activity', 'method' => 'specifyVote');
        $this->routes['activity_vote_post']        = array('url' => 'vote/post', 'controller' => 'Activity', 'method' => 'specifyVotePosted');
        $this->routes['cast_vote']                 = array('url' => 'vote/cast', 'controller' => 'Activity', 'method' => 'castVote');
        $this->routes['voting_done']               = array('url' => 'vote/done', 'controller' => 'Activity', 'method' => 'votingDone');
        $this->routes['activity_specific_vote']    = array('url' => 'vote/:code:', 'controller' => 'Activity', 'method' => 'confirmVote');

        $this->routes['gamestart_queue']           = array('url' => 'activities/gamestart-queue', 'controller' => 'Activity', 'method' => 'gamestartQueue');
        $this->routes['gamestart_queue_ajax']      = array('url' => 'activities/gamestart-queue-ajax', 'controller' => 'Activity', 'method' => 'gamestartQueueAjax');

        $this->routes['gamemaster_list_export']    = array('url' => 'activities/gm-assignment-list', 'controller' => 'Activity', 'method' => 'exportGameList');
        $this->routes['create_gm_briefings']       = array('url' => 'activities/create-gm-briefings', 'controller' => 'Activity', 'method' => 'createGmBriefings');

        // lokaler routes
        $this->routes['lokalerhome']         = array('url' => 'lokaler/', 'controller' => 'Rooms', 'method' => 'main');
        $this->routes['opret_lokale']        = array('url' => 'lokaler/create', 'controller' => 'Rooms', 'method' => 'create');
        $this->routes['vis_lokale']          = array('url' => 'lokaler/vis/:id:', 'controller' => 'Rooms', 'method' => 'visLokale');
        $this->routes['vis_alle_lokaler']    = array('url' => 'lokaler/all', 'controller' => 'Rooms', 'method' => 'visAlle');
        $this->routes['edit_lokale']         = array('url' => 'lokaler/edit/:id:', 'controller' => 'Rooms', 'method' => 'edit');
        $this->routes['slet_lokale']         = array('url' => 'lokaler/slet/:id:', 'controller' => 'Rooms', 'method' => 'deleteRoom');
        $this->routes['ajax_get_lokaler']    = array('url' => 'lokaler/getlokaler/:afvikling_id:', 'controller' => 'Rooms', 'method' => 'ajaxGetLokaler');
        $this->routes['lokale_brug']         = array('url' => 'lokaler/lokalebrug/:day:','controller' => 'Rooms', 'method' => 'roomUse');
        $this->routes['image_upload']        = array('url' => 'rooms/upload-images/:id:','controller' => 'Rooms', 'method' => 'uploadImages');
        $this->routes['room_image_overview'] = array('url' => 'rooms/image-overview','controller' => 'Rooms', 'method' => 'imageOverview');

        $this->routes['sleep_statistics']    = array('url' => 'rooms/sleepstatistics', 'controller' => 'Rooms', 'method' => 'sleepStatistics');

        // wear routes
        $this->routes['wearhome']                     = array('url' => 'wear/', 'controller' => 'Wear', 'method' => 'main');
        $this->routes['show_wear']                    = array('url' => 'wear/showwear', 'controller' => 'Wear', 'method' => 'showTypes');
        $this->routes['show_wear_ajax']               = array('url' => 'wear/showwear/ajax', 'controller' => 'Wear', 'method' => 'showTypesAjax');
        $this->routes['vis_wear']                     = array('url' => 'wear/viswear/:id:', 'controller' => 'Wear', 'method' => 'showWear');
        $this->routes['edit_wear']                    = array('url' => 'wear/editwear/:id:', 'controller' => 'Wear', 'method' => 'editWear');
        $this->routes['delete_wear']                  = array('url' => 'wear/deletewear/:id:', 'controller' => 'Wear', 'method' => 'deleteWear');
        $this->routes['create_wear']                  = array('url' => 'wear/createwear', 'controller' => 'Wear', 'method' => 'createWear');
        $this->routes['wear_attributes']              = array('url' => 'wear/attributes', 'controller' => 'Wear', 'method' => 'attributes');
        $this->routes['wear_breakdown']               = array('url' => 'wear/breakdown', 'controller' => 'Wear', 'method' => 'wearBreakdown');
        $this->routes['detailed_order_list']          = array('url' => 'wear/detailed', 'controller' => 'Wear', 'method' => 'detailedOrderList');
        $this->routes['detailed_unfilled_order_list'] = array('url' => 'wear/unfilled', 'controller' => 'Wear', 'method' => 'detailedUnfilledOrderList');
        $this->routes['detailed_ajax']                = array('url' => 'wear/detailed/ajax/', 'controller' => 'Wear', 'method' => 'detailedOrderAjax');
        $this->routes['detailed_order_list_print']    = array('url' => 'wear/detailed/print/', 'controller' => 'Wear', 'method' => 'detailedOrderListPrint');
        $this->routes['detailed_mini_list']           = array('url' => 'wear/detailed/:id:', 'controller' => 'Wear', 'method' => 'detailedMiniList');
        $this->routes['ajax_get_wear']                = array('url' => 'wear/ajaxgetwear/:id:', 'controller' => 'Wear', 'method' => 'ajaxGetWear');
        $this->routes['wear_handout']                 = array('url' => 'wear/handout', 'controller' => 'Wear', 'method' => 'displayHandout');
        $this->routes['wear_handout_ajax']            = array('url' => 'wear/handout/ajax', 'controller' => 'Wear', 'method' => 'ajaxHandout');
        $this->routes['wear_labels']                  = array('url' => 'wear/print-labels', 'controller' => 'Wear', 'method' => 'showPrintLabels');
        $this->routes['wear_upload_image']            = array('url' => 'wear/upload-image', 'controller' => 'Wear', 'method' => 'uploadImage');
        

        // mad routes
        $this->routes['madhome']              = array('url' => 'mad/', 'controller' => 'Food', 'method' => 'main');
        $this->routes['show_food_types']      = array('url' => 'mad/foodtypes/', 'controller' => 'Food', 'method' => 'showTypes');
        $this->routes['show_food']            = array('url' => 'mad/show/:id:', 'controller' => 'Food', 'method' => 'showFood');
        $this->routes['food_stats']           = array('url' => 'food/stats', 'controller' => 'Food', 'method' => 'foodStats');
        $this->routes['edit_food']            = array('url' => 'mad/edit/:id:', 'controller' => 'Food', 'method' => 'editFood');
        $this->routes['create_food']          = array('url' => 'mad/create/', 'controller' => 'Food', 'method' => 'createFood');
        $this->routes['delete_food']          = array('url' => 'mad/delete/:id:', 'controller' => 'Food', 'method' => 'deleteFood');
        $this->routes['show_tradeable_food']  = array('url' => 'mad/tradeable', 'controller' => 'Food', 'method' => 'showTradeable');

        $this->routes['ajax_get_madtider']    = array('url' => 'mad/ajaxgetmadtider/:id:', 'controller' => 'Food', 'method' => 'ajaxGetMadtider');
        $this->routes['food_handout']         = array('url' => 'mad/handout', 'controller' => 'Food', 'method' => 'displayHandout');
        $this->routes['food_handout_ajax']    = array('url' => 'mad/handout/ajax', 'controller' => 'Food', 'method' => 'ajaxHandout');

        $this->routes['reset_participant_foodtime'] = array('url' => 'food/reset-handout-times', 'controller' => 'Food', 'method' => 'resetParticipantHandoutTimes');

        // indgang routes
        $this->routes['indganghome']  = array('url' => 'indgang/', 'controller' => 'Entrance', 'method' => 'main');
        $this->routes['show_entries'] = array('url' => 'indgang/entrytypes/', 'controller' => 'Entrance', 'method' => 'entryTypes');
        $this->routes['show_entry']   = array('url' => 'indgang/entry/:id:', 'controller' => 'Entrance', 'method' => 'showType');
        $this->routes['create_entry'] = array('url' => 'indgang/createentry/', 'controller' => 'Entrance', 'method' => 'createEntry');
        $this->routes['edit_entry']   = array('url' => 'indgang/editentry/:id:', 'controller' => 'Entrance', 'method' => 'editEntry');
        $this->routes['delete_entry'] = array('url' => 'indgang/deleteentry/:id:', 'controller' => 'Entrance', 'method' => 'deleteEntry');
        $this->routes['entry_stats']  = array('url' => 'entry/stats', 'controller' => 'Entrance', 'method' => 'entryStats');

        // gds routes
        $this->routes['gdshome']                = array('url' => 'gds/', 'controller' => 'Gds', 'method' => 'main');
        $this->routes['gds_calendar_date']      = array('url' => 'gds/calendar/:date:', 'controller' => 'Gds', 'method' => 'viewDay');
        $this->routes['gds_categories']         = array('url' => 'gds/categories', 'controller' => 'Gds', 'method' => 'categories');
        $this->routes['gds_create_category']    = array('url' => 'gds/create-category', 'controller' => 'Gds', 'method' => 'createCategory');
        $this->routes['gds_category']           = array('url' => 'gds/category/:gds_id:', 'controller' => 'Gds', 'method' => 'editCategory');
        $this->routes['ajax_get_vagttider']     = array('url' => 'gds/ajaxvagttider/:deltager_id:/:gds_id:', 'controller' => 'Gds', 'method' => 'ajaxGetGDSTider');
        $this->routes['ajax_get_gds_periods']   = array('url' => 'gds/ajaxshiftperiods/:deltager_id:/:gds_id:', 'controller' => 'Gds', 'method' => 'ajaxGetGDSPeriods');
        $this->routes['ajax_get_vagtsignups']   = array('url' => 'gds/ajaxgetsignups/:vagt_id:', 'controller' => 'Gds', 'method' => 'ajaxGetSignups');
        $this->routes['ajax_add_to_shift']      = array('url' => 'gds/ajaxaddtoshift/:vagt_id:/:id_string:', 'controller' => 'Gds', 'method' => 'ajaxAddToShift');
        $this->routes['ajax_remove_from_shift'] = array('url' => 'gds/ajaxremovefromshift/:vagt_id:/:id_string:', 'controller' => 'Gds', 'method' => 'ajaxRemoveFromShift');
        $this->routes['ajax_mark_no_show']      = array('url' => 'gds/ajax-mark-noshow', 'controller' => 'Gds', 'method' => 'ajaxMarkNoshow');
        $this->routes['ajax_mark_contacted']    = array('url' => 'gds/ajax-mark-contacted', 'controller' => 'Gds', 'method' => 'ajaxMarkContacted');
        $this->routes['gds_sms_team']           = array('url' => 'gds/smsteam/:shift_id:', 'controller' => 'Participant', 'method' => 'smsTeamMembers');
        $this->routes['list_all_shifts']        = array('url' => 'gds/listshifts/:id:', 'controller' => 'Gds', 'method' => 'listShifts');
        $this->routes['external_list_all_shifts'] = array('url' => 'gds/listshifts/external/:hash:', 'controller' => 'Gds', 'method' => 'listShiftsExternal');
        $this->routes['get_gds_suggestions']    = array('url' => 'gds/shift-suggestions/:shift_id:', 'controller' => 'Gds', 'method' => 'getShiftSuggestions');
        $this->routes['gds_shift_participants'] = array('url' => 'gds/show-shift-participants/:shift_id:', 'controller' => 'Gds', 'method' => 'showShiftParticipants');

        // hold routes
        $this->routes['holdhome']                  = array('url' => 'hold/', 'controller' => 'Groups', 'method' => 'main');
        $this->routes['vis_alle_hold']             = array('url' => 'hold/visalle/', 'controller' => 'Groups', 'method' => 'visAlle');
        $this->routes['vis_hold']                  = array('url' => 'hold/vis/:id:', 'controller' => 'Groups', 'method' => 'visHold');
        $this->routes['opret_hold']                = array('url' => 'hold/new/', 'controller' => 'Groups', 'method' => 'createGroup');
        $this->routes['edit_hold']                 = array('url' => 'hold/edit/:id:', 'controller' => 'Groups', 'method' => 'edit');
        $this->routes['delete_hold']               = array('url' => 'hold/slet/:id:', 'controller' => 'Groups', 'method' => 'deleteGroup');
        $this->routes['ajax_delete_hold']          = array('url' => 'hold/ajaxdeletegroup/:id:', 'controller' => 'Groups', 'method' => 'ajaxDeleteGroup');
        $this->routes['ajax_create_group']         = array('url' => 'hold/ajaxcreategroup/:afvikling_id:', 'controller' => 'Groups', 'method' => 'ajaxCreateGroup');
        $this->routes['ajax_schedule_participant'] = array('url' => 'groups/scheduleparticipant', 'controller' => 'Groups', 'method' => 'ajaxScheduleParticipant');

        // log routes
        $this->routes['log']      = array('url' => 'log', 'controller' => 'Log', 'method' => 'showLog');
        $this->routes['log_ajax'] = array('url' => 'log/ajax/', 'controller' => 'Log', 'method' => 'ajaxList');

        // test routes
        $this->routes['test_signup'] = array('url' => 'deltager/testsignup/', 'controller' => 'Deltager', 'method' => 'test');

        // admin routes
        $this->routes['admin_options']              = array('url' => 'admin/', 'controller' => 'Admin', 'method' => 'main');
        $this->routes['admin_handle_users']         = array('url' => 'admin/users/', 'controller' => 'Admin', 'method' => 'handleUsers');
        $this->routes['admin_handle_roles']         = array('url' => 'admin/roles/', 'controller' => 'Admin', 'method' => 'handleRoles');
        $this->routes['admin_handle_privileges']    = array('url' => 'admin/privileges/', 'controller' => 'Admin', 'method' => 'handlePrivileges');
        $this->routes['admin_reset_signup_confirm'] = array('url' => 'admin/reset/confirm', 'controller' => 'Admin', 'method' => 'showConfirmReset');
        $this->routes['admin_reset_signup_execute'] = array('url' => 'admin/reset/execute', 'controller' => 'Admin', 'method' => 'resetSignups');

        // admin ajax routes
        $this->routes['admin_ajax_changepass']      = array('url' => 'admin/ajax/changepass/:id:', 'controller' => 'Admin', 'method' => 'ajaxChangePass');
        $this->routes['admin_ajax_changelabel']     = array('url' => 'admin/ajax/changelabel/:id:', 'controller' => 'Admin', 'method' => 'ajaxChangeLabel');
        $this->routes['admin_ajax_removerole']      = array('url' => 'admin/ajax/removerole/:id:/:role_id:', 'controller' => 'Admin', 'method' => 'ajaxRemoveRole');
        $this->routes['admin_ajax_addrole']         = array('url' => 'admin/ajax/addrole/:id:/:role_id:', 'controller' => 'Admin', 'method' => 'ajaxAddRole');
        $this->routes['admin_ajax_disableuser']     = array('url' => 'admin/ajax/disableuser/:id:', 'controller' => 'Admin', 'method' => 'ajaxDisableUser');
        $this->routes['admin_ajax_enableuser']      = array('url' => 'admin/ajax/enableuser/:id:', 'controller' => 'Admin', 'method' => 'ajaxEnableUser');
        $this->routes['admin_ajax_deleteuser']      = array('url' => 'admin/ajax/deleteuser/:id:', 'controller' => 'Admin', 'method' => 'ajaxDeleteUser');
        $this->routes['admin_ajax_createuser']      = array('url' => 'admin/ajax/createuser/', 'controller' => 'Admin', 'method' => 'ajaxCreateUser');
        $this->routes['admin_ajax_createprivilege'] = array('url' => 'admin/ajax/createprivilege', 'controller' => 'Admin', 'method' => 'ajaxCreatePrivilege');
        $this->routes['admin_ajax_addprivilege']    = array('url' => 'admin/ajax/addprivilege/:role_id:/:privilege_id:', 'controller' => 'Admin', 'method' => 'ajaxAddPrivilege');
        $this->routes['admin_ajax_removeprivilege'] = array('url' => 'admin/ajax/removeprivilege/:role_id:/:privilege_id:', 'controller' => 'Admin', 'method' => 'ajaxRemovePrivilege');
        $this->routes['admin_ajax_deleteprivilege'] = array('url' => 'admin/ajax/deleteprivilege/:id:', 'controller' => 'Admin', 'method' => 'ajaxDeletePrivilege');
        $this->routes['admin_ajax_deleterole']      = array('url' => 'admin/ajax/deleterole/:id:', 'controller' => 'Admin', 'method' => 'ajaxDeleteRole');
        $this->routes['admin_ajax_createrole']      = array('url' => 'admin/ajax/createrole', 'controller' => 'Admin', 'method' => 'ajaxCreateRole');

        // api routes
        $this->routes['api_auth']                = array('url' => 'api/auth', 'controller' => 'Api', 'method' => 'auth');
        $this->routes['api_activities_short']    = array('url' => 'api/activities', 'controller' => 'Api', 'method' => 'activities');
        $this->routes['api_activities']          = array('url' => 'api/activities/:id:', 'controller' => 'Api', 'method' => 'activities');
        $this->routes['api_activities_app']      = array('url' => 'api/app/activities/:id:', 'controller' => 'Api', 'method' => 'activitiesForApp');
        $this->routes['api_activities_app_v']    = array('url' => 'api/app/v:version:/activities/:id:', 'controller' => 'Api', 'method' => 'activitiesForAppVersioned');
        $this->routes['api_activities_app_v2']   = array('url' => 'api/app/v2/activities/:id:', 'controller' => 'Api', 'method' => 'activitiesForAppV2');
        $this->routes['api_activities_by_field'] = array('url' => 'api/activities/:field:/:value:', 'controller' => 'Api', 'method' => 'activitiesByField');
        $this->routes['api_all_activities']      = array('url' => 'api/activities/all/:id:', 'controller' => 'Api', 'method' => 'allActivities');
        $this->routes['api_activity_schedules']  = array('url' => 'api/schedules/:id:', 'controller' => 'Api', 'method' => 'schedules');
        $this->routes['api_gds']                 = array('url' => 'api/gds/:id:', 'controller' => 'Api', 'method' => 'gds');
        $this->routes['api_gdscategories']       = array('url' => 'api/gdscategories/:id:', 'controller' => 'Api', 'method' => 'gdsCategories');
        $this->routes['api_gdsshift']            = array('url' => 'api/gdsshift/:id:', 'controller' => 'Api', 'method' => 'gdsShift');
        $this->routes['api_food']                = array('url' => 'api/food/:id:', 'controller' => 'Api', 'method' => 'food');
        $this->routes['api_entrance']            = array('url' => 'api/entrance/:id:', 'controller' => 'Api', 'method' => 'entrance');
        $this->routes['api_wear']                = array('url' => 'api/wear/:id:', 'controller' => 'Api', 'method' => 'wear');
        $this->routes['api_graph_wear']          = array('url' => 'api/graph/:name:', 'controller' => 'Api', 'method' => 'fetchGraphData');
        $this->routes['api_create_participant']  = array('url' => 'api/participant/create', 'controller' => 'Api', 'method' => 'createParticipant');
        $this->routes['api_set_wear']            = array('url' => 'api/participant/addwear', 'controller' => 'Api', 'method' => 'addWear');
        $this->routes['api_set_gds']             = array('url' => 'api/participant/addgds', 'controller' => 'Api', 'method' => 'addGDS');
        $this->routes['api_set_activity']        = array('url' => 'api/participant/addactivity', 'controller' => 'Api', 'method' => 'addActivity');
        $this->routes['api_set_entrance']        = array('url' => 'api/participant/addentrance', 'controller' => 'Api', 'method' => 'addEntrance');
        $this->routes['api_parse_signup']        = array('url' => 'api/participant/signup', 'controller' => 'Api', 'method' => 'parseSignup');
        $this->routes['api_activity_structure']  = array('url' => 'api/activity-structure', 'controller' => 'Api', 'method' => 'activityStructure');
        $this->routes['api_user_schedules']      = array('url' => 'api/user/:id:', 'controller' => 'Api', 'method' => 'getUserSchedule');
        $this->routes['api_user_schedules_v']    = array('url' => 'api/v:version:/user/:id:', 'controller' => 'Api', 'method' => 'getUserScheduleVersioned');
        $this->routes['api_user_schedules_v2']   = array('url' => 'api/v2/user/:id:', 'controller' => 'Api', 'method' => 'getUserScheduleV2');
        $this->routes['api_user_data_v']         = array('url' => 'api/v:version:/user-data/:email:', 'controller' => 'Api', 'method' => 'getUserDataVersioned');
        $this->routes['api_user_data']           = array('url' => 'api/v2/user-data/:email:', 'controller' => 'Api', 'method' => 'getUserData');
        $this->routes['api_get_messages']        = array('url' => 'api/messages/:id:', 'controller' => 'Api', 'method' => 'getUserMessages');
        $this->routes['api_user_register']       = array('url' => 'api/user/:id:/register', 'controller' => 'Api', 'method' => 'registerApp');
        $this->routes['api_user_unregister']     = array('url' => 'api/user/:id:/unregister', 'controller' => 'Api', 'method' => 'unregisterApp');
        $this->routes['api_user_data_v']         = array('url' => 'api/v:version:/confirmation-data', 'controller' => 'Api', 'method' => 'getConfirmationData');
        $this->routes['api_boardgames']          = array('url' => 'api/v:version:/boardgames', 'controller' => 'Api', 'method' => 'getBoardgameData');
        $this->routes['api_boardgame_loans']     = array('url' => 'api/boardgameloans/:id:', 'controller' => 'Api', 'method' => 'getBoardgameLoans');

        $this->routes['api_request_password_reminder'] = array('url' => 'api/request-password-email', 'controller' => 'Api', 'method' => 'requestPasswordReminder');

        // shop
        $this->routes['shop_overview']               = array('url' => 'shop', 'controller' => 'Shop', 'method' => 'overview');
        $this->routes['shop_parse_spreadsheet_data'] = array('url' => 'shop/parsedata', 'controller' => 'Shop', 'method' => 'parseSpreadsheetData');
        $this->routes['shop_single_update']          = array('url' => 'shop/ajaxupdate', 'controller' => 'Shop', 'method' => 'ajaxUpdate');
        $this->routes['shop_delete_product']         = array('url' => 'shop/deleteproduct', 'controller' => 'Shop', 'method' => 'deleteProduct');
        $this->routes['shop_product_graph_data']     = array('url' => 'shop/product-data/:id:', 'controller' => 'Shop', 'method' => 'fetchProductStats');

        // boardgames
        $this->routes['boardgames_overview']         = array('url' => 'boardgames', 'controller' => 'Boardgames', 'method' => 'overview');
        $this->routes['boardgames_data']             = array('url' => 'boardgames/data', 'controller' => 'Boardgames', 'method' => 'fetchData');
        $this->routes['boardgames_create']           = array('url' => 'boardgames/create', 'controller' => 'Boardgames', 'method' => 'createGame');
        $this->routes['boardgames_update']           = array('url' => 'boardgames/update', 'controller' => 'Boardgames', 'method' => 'updateGame');
        $this->routes['boardgames_edit']             = array('url' => 'boardgames/edit', 'controller' => 'Boardgames', 'method' => 'editGame');
        $this->routes['boardgames_parse']            = array('url' => 'boardgames/parse', 'controller' => 'Boardgames', 'method' => 'parseSpreadsheet');
        $this->routes['boardgames_update_note']      = array('url' => 'boardgames/update-note', 'controller' => 'Boardgames', 'method' => 'updateNote');
        $this->routes['boardgames_presence_check']   = array('url' => 'boardgames/presence-check', 'controller' => 'Boardgames', 'method' => 'presenceCheck');
        $this->routes['boardgames_presence_update']  = array('url' => 'boardgames/presence-update', 'controller' => 'Boardgames', 'method' => 'presenceUpdate');
        $this->routes['boardgames_presence_reset']   = array('url' => 'boardgames/presence-reset', 'controller' => 'Boardgames', 'method' => 'resetPresence');
        $this->routes['boardgames_reporting']        = array('url' => 'boardgames/reporting', 'controller' => 'Boardgames', 'method' => 'showReporting');

        // loans
        $this->routes['loans_overview']             = array('url' => 'loans', 'controller' => 'Loans', 'method' => 'overview');
        $this->routes['loans_data']                 = array('url' => 'loans/data', 'controller' => 'Loans', 'method' => 'fetchData');
        $this->routes['loans_create']               = array('url' => 'loans/create', 'controller' => 'Loans', 'method' => 'createItem');
        $this->routes['loans_update']               = array('url' => 'loans/update', 'controller' => 'Loans', 'method' => 'updateItem');
        $this->routes['loans_edit']                 = array('url' => 'loans/edit', 'controller' => 'Loans', 'method' => 'editItem');
        $this->routes['loans_parse']                = array('url' => 'loans/parse', 'controller' => 'Loans', 'method' => 'parseSpreadsheet');
        $this->routes['loans_update_note']          = array('url' => 'loans/update-note', 'controller' => 'Loans', 'method' => 'updateNote');

        // Signup Admin
        $this->routes['signup_pages']               = array('url' => 'signup/pages', 'controller' => 'SignupAdmin', 'method' => 'signupPages');
        $this->routes['signup_pages_specific']      = array('url' => 'signup/pages/:page:', 'controller' => 'SignupAdmin', 'method' => 'signupPages');
        $this->routes['signup_pages_add_element']   = array('url' => 'signup/pages/add-element', 'controller' => 'SignupAdmin', 'method' => 'addPageElement');
        $this->routes['signup_pages_edit_text']     = array('url' => 'signup/pages/edit-text', 'controller' => 'SignupAdmin', 'method' => 'editText');
        $this->routes['signup_config']              = array('url' => 'signup/config/:module:', 'controller' => 'SignupAdmin', 'method' => 'signupConfig');

        // Signup API
        $this->routes['api_signup_config']          = array('url' => 'api/signup/config/:module:', 'controller' => 'SignupApi', 'method' => 'getConfig');
        $this->routes['api_signup_page_list']       = array('url' => 'api/signup/pagelist', 'controller' => 'SignupApi', 'method' => 'getPageList');
        $this->routes['api_signup_page']            = array('url' => 'api/signup/page/:page_id:', 'controller' => 'SignupApi', 'method' => 'getPage');
        $this->routes['api_signup_food']            = array('url' => 'api/signup/food', 'controller' => 'SignupApi', 'method' => 'getFood');
        $this->routes['api_signup_activities']      = array('url' => 'api/signup/activities', 'controller' => 'SignupApi', 'method' => 'getActivities');
        $this->routes['api_signup_wear']            = array('url' => 'api/signup/wear', 'controller' => 'SignupApi', 'method' => 'getWear');
        $this->routes['api_signup_submit']          = array('url' => 'api/signup/submit', 'controller' => 'SignupApi', 'method' => 'submitSignup');
        $this->routes['api_signup_confirm']         = array('url' => 'api/signup/confirm', 'controller' => 'SignupApi', 'method' => 'confirmSignup');
        $this->routes['api_signup_load']            = array('url' => 'api/signup/load', 'controller' => 'SignupApi', 'method' => 'loadSignup');

        // Ticket system
        $this->routes['tickets_main']               = array('url' => 'tickets', 'controller' => 'Tickets', 'method' => 'mainPage');
        $this->routes['tickets_show']               = array('url' => 'tickets/show/:ticket_id:', 'controller' => 'Tickets', 'method' => 'mainPage');
        $this->routes['tickets_ajax']               = array('url' => 'tickets/ajax', 'controller' => 'Tickets', 'method' => 'ajaxTickets');
        $this->routes['tickets_messages_ajax']      = array('url' => 'tickets/:ticket_id:/messages', 'controller' => 'Tickets', 'method' => 'ajaxMessages');

        // Translations
        $this->routes['translations_ajax']           = array('url' => 'translations/ajax/:label:', 'controller' => 'Translation', 'method' => 'ajaxTranslations');

    }

    /**
     * adds a temporary route to the routes
     *
     * @param string $name - name of the route
     * @param string $url - the route itself
     * @param string $controller - name of the controller
     * @param string $method - name of the method to call
     * @access public
     * @return bool
     */
    public function addRoute($name, $url, $controller, $method = 'main')
    {
        if (!is_string($name) || !empty($this->routes[$name]) || !is_string($url) || !is_string($controller))
        {
            return false;
        }
        $this->routes[$name] = array($url, $controller, $method);
        return true;
    }


    /**
     * returns a given route if it is set
     *
     * @param string $name - name of the route to return
     * @access public
     * @return array
     */
    public function getRoute($name)
    {
        if (!is_string($name) || empty($this->routes[$name]))
        {
            return array();
        }
        return $this->routes[$name];
    }

    /**
     * looks for a matching route in the set routes
     *
     * @param string $uri - uri to check
     * @access public
     * @return array - empty if nothing found, otherwise full of good stuff
     */
    public function matchRoute($uri)
    {
        if (!empty($this->matches[base64_encode($uri)]))
        {
            return $this->matches[base64_encode($uri)];
        }
        $match = array();
        $matchvars = array();
        foreach ($this->routes as $route)
        {
            $url = preg_replace(array('/\//','/\*/','/:[^:]+:/','/\\?\/$/'), array('\/','.*','([^\/]+)',''),   $route['url']);
            $url = "/^{$url}\\/?$/i";
            if (!preg_match($url, $uri, $matches))
            {
                continue;
            }
            if (empty($match) || count(explode('/', $route['url'])) > count(explode('/', $match['url'])) || (count(explode('/', $route['url'])) == count(explode('/', $match['url'])) && count($matches) <= count($matchvars)) || count(explode('/', $route['url'])) > count(explode('/', $match['url'])))
            {
                $match = $route;
                $matchvars = $matches;
            }
        }

        // check if any vars were sent through the request
        if (!empty($match) && preg_match_all('/:([^:]+?):/', $match['url'], $vars, PREG_SET_ORDER))
        {
            if (count($vars) == (count($matchvars) - 1))
            {
                $i = 1;
                $match['vars'] = array();
                foreach ($vars as $varname)
                {
                    $match['vars'][$varname[1]] = $matchvars[$i];
                    $i++;
                }
            }
        }
        $this->matches[base64_encode($uri)] = $match;
        return $match;
    }

    /**
     * returns a url string, based on the routes url but with with placeholders replaced
     *
     * @param string $route - name of route to get url for
     * @param array  $vars  - vars to use instead of placeholders
     *
     * @access public
     * @return string
     */
    public function url($route, $vars = array())
    {
        $route = $this->getRoute($route);
        if (empty($route))
        {
            return '';
        }
        $url = $route['url'];
        $placeholders = preg_match_all('/:([^:]+?):/', $url, $matches, PREG_SET_ORDER);
        if ($placeholders)
        {
            $keys = array_keys($vars);
            foreach ($matches as $match)
            {
                if (in_array($match[1], $keys))
                {
                    $url = str_replace($match[0], $vars[$match[1]], $url);
                }
                $placeholders--;
            }
            if ($placeholders != 0)
            {
                return '';
            }
        }
        return $this->config->get('app.public_uri') . $url;
    }

}
