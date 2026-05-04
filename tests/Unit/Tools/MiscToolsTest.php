<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\MiscTools;

class MiscToolsTest extends AbstractToolsTest
{
    private MiscTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new MiscTools($this->apiClient);
    }

    public function testGetDefinitionsReturnsArray(): void
    {
        self::assertNotEmpty($this->tools->getDefinitions());
    }

    public function testAllDefinitionsHaveNameAndDescription(): void
    {
        foreach ($this->tools->getDefinitions() as $def) {
            self::assertArrayHasKey('name', $def);
            self::assertArrayHasKey('description', $def);
        }
    }

    public function testGetOrganizationUsesGet(): void
    {
        $this->addOk('{"id":1,"name":"Test Club"}');
        $this->tools->dispatch('getOrganization', ['token' => 'tok']);
        $this->assertGetTo('/organization/');
    }

    public function testListCustomFieldsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCustomFields', ['token' => 'tok']);
        $this->assertGetTo('/custom-field/');
    }

    public function testListDocumentTemplatesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listDocumentTemplates', ['token' => 'tok']);
        $this->assertGetTo('/document-template/');
    }

    public function testListCalendarsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCalendars', ['token' => 'tok']);
        $this->assertGetTo('/calendar/');
    }

    public function testListLocationsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listLocations', ['token' => 'tok']);
        $this->assertGetTo('/location/');
    }

    public function testGetWastebasketUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listWastebasket', ['token' => 'tok']);
        $this->assertGetTo('/wastebasket/');
    }

    // ── custom-field category ─────────────────────────────────────────────────

    public function testGetCustomFieldUsesCorrectPath(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getCustomField', ['token' => 'tok', 'id' => 1]);
        $this->assertGetTo('/custom-field/1/');
    }

    public function testCreateCustomFieldUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createCustomField', ['token' => 'tok', 'name' => 'Shirt Size']);
        $this->assertPostTo('/custom-field/');
        $this->assertBodyContains('name', 'Shirt Size');
    }

    public function testUpdateCustomFieldUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateCustomField', ['token' => 'tok', 'id' => 1, 'name' => 'Updated']);
        $this->assertPatchTo('/custom-field/1/');
    }

    public function testDeleteCustomFieldReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteCustomField', ['token' => 'tok', 'id' => 1]);
        $this->assertDeletedMessage($result);
    }

    // ── document-template category ────────────────────────────────────────────

    public function testGetDocumentTemplateUsesCorrectPath(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('getDocumentTemplate', ['token' => 'tok', 'id' => 2]);
        $this->assertGetTo('/document-template/2/');
    }

    public function testCreateDocumentTemplateUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createDocumentTemplate', ['token' => 'tok', 'name' => 'Welcome Letter']);
        $this->assertPostTo('/document-template/');
        $this->assertBodyContains('name', 'Welcome Letter');
    }

    public function testUpdateDocumentTemplateUsesPatch(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('updateDocumentTemplate', ['token' => 'tok', 'id' => 2, 'name' => 'Invoice']);
        $this->assertPatchTo('/document-template/2/');
    }

    public function testDeleteDocumentTemplateReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteDocumentTemplate', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    // ── calendar category ─────────────────────────────────────────────────────

    public function testGetCalendarUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getCalendar', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/calendar/3/');
    }

    public function testCreateCalendarUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createCalendar', ['token' => 'tok', 'name' => 'Events']);
        $this->assertPostTo('/calendar/');
        $this->assertBodyContains('name', 'Events');
    }

    public function testUpdateCalendarUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateCalendar', ['token' => 'tok', 'id' => 3, 'color' => '#FF0000']);
        $this->assertPatchTo('/calendar/3/');
    }

    public function testDeleteCalendarReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteCalendar', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── location category ─────────────────────────────────────────────────────

    public function testGetLocationUsesCorrectPath(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('getLocation', ['token' => 'tok', 'id' => 4]);
        $this->assertGetTo('/location/4/');
    }

    public function testCreateLocationUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createLocation', ['token' => 'tok', 'name' => 'Club House']);
        $this->assertPostTo('/location/');
        $this->assertBodyContains('name', 'Club House');
    }

    public function testDeleteLocationReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteLocation', ['token' => 'tok', 'id' => 4]);
        $this->assertDeletedMessage($result);
    }

    // ── voting category ───────────────────────────────────────────────────────

    public function testListVotingsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listVotings', ['token' => 'tok']);
        $this->assertGetTo('/voting/');
    }

    public function testCreateVotingUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createVoting', ['token' => 'tok', 'name' => 'Annual Vote']);
        $this->assertPostTo('/voting/');
        $this->assertBodyContains('name', 'Annual Vote');
    }

    // ── organization category ─────────────────────────────────────────────────

    public function testUpdateOrganizationUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateOrganization', ['token' => 'tok', 'name' => 'My Club']);
        $this->assertPatchTo('/organization/');
        $this->assertBodyContains('name', 'My Club');
    }

    public function testGetOrganizationSettingsUsesGet(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getOrganizationSettings', ['token' => 'tok']);
        $this->assertGetTo('/organization-settings/');
    }

    public function testUpdateOrganizationSettingsUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateOrganizationSettings', ['token' => 'tok', 'enable_tasks' => true]);
        $this->assertPatchTo('/organization-settings/');
    }

    // ── price-group category ──────────────────────────────────────────────────

    public function testListPriceGroupsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPriceGroups', ['token' => 'tok']);
        $this->assertGetTo('/price-group/');
    }

    public function testGetPriceGroupUsesCorrectPath(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('getPriceGroup', ['token' => 'tok', 'id' => 5]);
        $this->assertGetTo('/price-group/5/');
    }

    public function testCreatePriceGroupUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPriceGroup', ['token' => 'tok', 'name' => 'Standard']);
        $this->assertPostTo('/price-group/');
    }

    public function testDeletePriceGroupReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePriceGroup', ['token' => 'tok', 'id' => 5]);
        $this->assertDeletedMessage($result);
    }

    // ── accounting-plan / notification-log / wastebasket ─────────────────────

    public function testListAccountingPlansUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listAccountingPlans', ['token' => 'tok']);
        $this->assertGetTo('/accounting-plan/');
    }

    public function testListNotificationLogsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listNotificationLogs', ['token' => 'tok']);
        $this->assertGetTo('/notification-log/');
    }

    // ── refreshToken ──────────────────────────────────────────────────────────

    public function testRefreshTokenUsesGet(): void
    {
        $this->addOk('{"Bearer":"new-token"}');
        $this->tools->dispatch('refreshToken', ['token' => 'tok']);
        $this->assertGetTo('/refresh-token/');
    }

    // ── select-option category ────────────────────────────────────────────────

    public function testListSelectOptionsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listSelectOptions', ['token' => 'tok']);
        $this->assertGetTo('/select-option/');
    }

    public function testCreateSelectOptionUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createSelectOption', ['token' => 'tok', 'value' => 'Yes']);
        $this->assertPostTo('/select-option/');
        $this->assertBodyContains('value', 'Yes');
    }

    // ── session-filter category ───────────────────────────────────────────────

    public function testListSessionFiltersUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listSessionFilters', ['token' => 'tok']);
        $this->assertGetTo('/session-filter/');
    }

    public function testCreateSessionFilterUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createSessionFilter', ['token' => 'tok', 'userFilter' => 'active']);
        $this->assertPostTo('/session-filter/');
    }

    // ── custom-filter category ────────────────────────────────────────────────

    public function testListCustomFiltersUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCustomFilters', ['token' => 'tok']);
        $this->assertGetTo('/custom-filter/');
    }

    public function testCreateCustomFilterUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createCustomFilter', ['token' => 'tok', 'name' => 'My Filter']);
        $this->assertPostTo('/custom-filter/');
    }

    // ── custom-field-collection category ─────────────────────────────────────

    public function testListCustomFieldCollectionsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCustomFieldCollections', ['token' => 'tok']);
        $this->assertGetTo('/custom-field-collection/');
    }

    public function testCreateCustomFieldCollectionUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createCustomFieldCollection', ['token' => 'tok', 'name' => 'Profile Fields']);
        $this->assertPostTo('/custom-field-collection/');
    }

    // ── document-template-settings ────────────────────────────────────────────

    public function testGetDocumentTemplateSettingsUsesGet(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getDocumentTemplateSettings', ['token' => 'tok']);
        $this->assertGetTo('/document-template-settings/');
    }

    public function testUpdateDocumentTemplateSettingsUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateDocumentTemplateSettings', ['token' => 'tok', 'css' => 'body{}']);
        $this->assertPatchTo('/document-template-settings/');
    }

    // ── page-template category ────────────────────────────────────────────────

    public function testListPageTemplatesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPageTemplates', ['token' => 'tok']);
        $this->assertGetTo('/page-template/');
    }

    public function testCreatePageTemplateUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPageTemplate', ['token' => 'tok', 'name' => 'Default']);
        $this->assertPostTo('/page-template/');
    }

    // ── anniversary-mailing category ──────────────────────────────────────────

    public function testListAnniversaryMailingsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listAnniversaryMailings', ['token' => 'tok']);
        $this->assertGetTo('/anniversary-mailing/');
    }

    public function testCreateAnniversaryMailingUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createAnniversaryMailing', ['token' => 'tok', 'name' => 'Birthday Mail', 'subject' => 'Happy Birthday']);
        $this->assertPostTo('/anniversary-mailing/');
    }

    // ── public-chat-room category ─────────────────────────────────────────────

    public function testListPublicChatRoomsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPublicChatRooms', ['token' => 'tok']);
        $this->assertGetTo('/public-chat-room/');
    }

    public function testCreatePublicChatRoomUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPublicChatRoom', ['token' => 'tok', 'name' => 'General']);
        $this->assertPostTo('/public-chat-room/');
    }

    // ── website ───────────────────────────────────────────────────────────────

    public function testGetWebsiteUsesGet(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getWebsite', ['token' => 'tok']);
        $this->assertGetTo('/website/');
    }

    public function testUpdateWebsiteUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateWebsite', ['token' => 'tok', 'name' => 'Club Site']);
        $this->assertPatchTo('/website/');
    }

    // ── chat-settings ─────────────────────────────────────────────────────────

    public function testGetChatSettingsUsesGet(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getChatSettings', ['token' => 'tok']);
        $this->assertGetTo('/chat-settings/');
    }

    public function testUpdateChatSettingsUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateChatSettings', ['token' => 'tok', 'member_can_write' => true]);
        $this->assertPatchTo('/chat-settings/');
    }

    // ── smtp-email-setting category ───────────────────────────────────────────

    public function testListSmtpEmailSettingsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listSmtpEmailSettings', ['token' => 'tok']);
        $this->assertGetTo('/smtp-email-setting/');
    }

    public function testCreateSmtpEmailSettingUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createSmtpEmailSetting', ['token' => 'tok', 'name' => 'Main SMTP', 'host' => 'smtp.example.com']);
        $this->assertPostTo('/smtp-email-setting/');
        $this->assertBodyContains('host', 'smtp.example.com');
    }

    // ── chairman-level category ───────────────────────────────────────────────

    public function testListChairmanLevelsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listChairmanLevels', ['token' => 'tok']);
        $this->assertGetTo('/chairman-level/');
    }

    public function testCreateChairmanLevelUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createChairmanLevel', ['token' => 'tok', 'name' => 'President']);
        $this->assertPostTo('/chairman-level/');
    }

    // ── voting-question category ──────────────────────────────────────────────

    public function testListVotingQuestionsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listVotingQuestions', ['token' => 'tok']);
        $this->assertGetTo('/voting-question/');
    }

    public function testCreateVotingQuestionUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createVotingQuestion', ['token' => 'tok', 'question' => 'Approve budget?']);
        $this->assertPostTo('/voting-question/');
    }

    // ── chairman-note category ────────────────────────────────────────────────

    public function testListChairmanNotesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listChairmanNotes', ['token' => 'tok']);
        $this->assertGetTo('/chairman-note/');
    }

    public function testCreateChairmanNoteUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createChairmanNote', ['token' => 'tok', 'text' => 'A note']);
        $this->assertPostTo('/chairman-note/');
    }

    // ── oauth2-custom-claim category ──────────────────────────────────────────

    public function testListOauth2CustomClaimsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listOauth2CustomClaims', ['token' => 'tok']);
        $this->assertGetTo('/oauth2-custom-claim/');
    }

    public function testCreateOauth2CustomClaimUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createOauth2CustomClaim', ['token' => 'tok', 'claim_name' => 'role']);
        $this->assertPostTo('/oauth2-custom-claim/');
    }

    // ── organization-token category ───────────────────────────────────────────

    public function testListOrganizationTokensUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listOrganizationTokens', ['token' => 'tok']);
        $this->assertGetTo('/organization-token/');
    }

    public function testCreateOrganizationTokenUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createOrganizationToken', ['token' => 'tok', 'name' => 'API Token']);
        $this->assertPostTo('/organization-token/');
    }

    // ── oauth2-application category ───────────────────────────────────────────

    public function testListOauth2ApplicationsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listOauth2Applications', ['token' => 'tok']);
        $this->assertGetTo('/oauth2-application/');
    }

    public function testCreateOauth2ApplicationUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createOauth2Application', ['token' => 'tok', 'name' => 'My App']);
        $this->assertPostTo('/oauth2-application/');
    }

    // ── oauth-credentials category ────────────────────────────────────────────

    public function testListOauthCredentialsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listOauthCredentials', ['token' => 'tok']);
        $this->assertGetTo('/oauth-credentials/');
    }

    public function testCreateOauthCredentialsUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createOauthCredentials', ['token' => 'tok', 'name' => 'Google']);
        $this->assertPostTo('/oauth-credentials/');
    }

    // ── lsb-sport / dosb-sport categories ────────────────────────────────────

    public function testListLsbSportsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listLsbSports', ['token' => 'tok']);
        $this->assertGetTo('/lsb-sport/');
    }

    public function testCreateLsbSportUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createLsbSport', ['token' => 'tok', 'title' => 'Soccer']);
        $this->assertPostTo('/lsb-sport/');
    }

    public function testListDosbSportsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listDosbSports', ['token' => 'tok']);
        $this->assertGetTo('/dosb-sport/');
    }

    public function testCreateDosbSportUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createDosbSport', ['token' => 'tok', 'title' => 'Tennis']);
        $this->assertPostTo('/dosb-sport/');
    }

    // ── file-system-path category ─────────────────────────────────────────────

    public function testListFileSystemPathsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listFileSystemPaths', ['token' => 'tok']);
        $this->assertGetTo('/file-system-path/');
    }

    public function testCreateFileSystemPathUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createFileSystemPath', ['token' => 'tok', 'name' => 'uploads', 'path' => '/var/www/uploads']);
        $this->assertPostTo('/file-system-path/');
    }

    // ── updateFormerMemberData ────────────────────────────────────────────────

    public function testUpdateFormerMemberDataUsesPatch(): void
    {
        $this->addOk('{"id":10}');
        $this->tools->dispatch('updateFormerMemberData', ['token' => 'tok', 'id' => 10, 'join_date' => '2020-01-01']);
        $this->assertPatchTo('/former-member-data/10/');
        $this->assertBodyContains('join_date', '2020-01-01');
    }

    // ── resetPassword ─────────────────────────────────────────────────────────

    public function testResetPasswordUsesPost(): void
    {
        $this->addOk('{"status":"ok"}');
        $this->tools->dispatch('resetPassword', ['token' => 'tok', 'email' => 'user@example.com']);
        $this->assertPostTo('/reset-password/');
    }

    // ── getToken ──────────────────────────────────────────────────────────────

    public function testGetTokenUsesPost(): void
    {
        $this->addOk('{"token":"abc"}');
        $this->tools->dispatch('getToken', ['token' => 'tok', 'username' => 'admin', 'password' => 'secret']);
        $this->assertPostTo('/get-token/');
    }

    // ── getVoting / updateVoting / deleteVoting ───────────────────────────────

    public function testGetVotingUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getVoting', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/voting/3/');
    }

    public function testUpdateVotingUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateVoting', ['token' => 'tok', 'id' => 3, 'name' => 'Updated Vote']);
        $this->assertPatchTo('/voting/3/');
        $this->assertBodyContains('name', 'Updated Vote');
    }

    public function testDeleteVotingReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteVoting', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── updateLocation ────────────────────────────────────────────────────────

    public function testUpdateLocationUsesPatch(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('updateLocation', ['token' => 'tok', 'id' => 5, 'name' => 'New Hall']);
        $this->assertPatchTo('/location/5/');
        $this->assertBodyContains('name', 'New Hall');
    }

    // ── getSelectOption / updateSelectOption / deleteSelectOption ─────────────

    public function testGetSelectOptionUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getSelectOption', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/select-option/7/');
    }

    public function testUpdateSelectOptionUsesPatch(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('updateSelectOption', ['token' => 'tok', 'id' => 7, 'value' => 'Option B']);
        $this->assertPatchTo('/select-option/7/');
        $this->assertBodyContains('value', 'Option B');
    }

    public function testDeleteSelectOptionReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteSelectOption', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    // ── getSessionFilter / updateSessionFilter / deleteSessionFilter ──────────

    public function testGetSessionFilterUsesCorrectPath(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('getSessionFilter', ['token' => 'tok', 'id' => 2]);
        $this->assertGetTo('/session-filter/2/');
    }

    public function testUpdateSessionFilterUsesPatch(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('updateSessionFilter', ['token' => 'tok', 'id' => 2, 'userFilter' => 'active']);
        $this->assertPatchTo('/session-filter/2/');
        $this->assertBodyContains('userFilter', 'active');
    }

    public function testDeleteSessionFilterReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteSessionFilter', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    // ── getCustomFilter / updateCustomFilter / deleteCustomFilter ─────────────

    public function testGetCustomFilterUsesCorrectPath(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('getCustomFilter', ['token' => 'tok', 'id' => 4]);
        $this->assertGetTo('/custom-filter/4/');
    }

    public function testUpdateCustomFilterUsesPatch(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('updateCustomFilter', ['token' => 'tok', 'id' => 4, 'name' => 'Active Members']);
        $this->assertPatchTo('/custom-filter/4/');
        $this->assertBodyContains('name', 'Active Members');
    }

    public function testDeleteCustomFilterReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteCustomFilter', ['token' => 'tok', 'id' => 4]);
        $this->assertDeletedMessage($result);
    }

    // ── getCustomFieldCollection / updateCustomFieldCollection / delete ────────

    public function testGetCustomFieldCollectionUsesCorrectPath(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('getCustomFieldCollection', ['token' => 'tok', 'id' => 6]);
        $this->assertGetTo('/custom-field-collection/6/');
    }

    public function testUpdateCustomFieldCollectionUsesPatch(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('updateCustomFieldCollection', ['token' => 'tok', 'id' => 6, 'name' => 'General']);
        $this->assertPatchTo('/custom-field-collection/6/');
        $this->assertBodyContains('name', 'General');
    }

    public function testDeleteCustomFieldCollectionReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteCustomFieldCollection', ['token' => 'tok', 'id' => 6]);
        $this->assertDeletedMessage($result);
    }

    // ── getPageTemplate / updatePageTemplate / deletePageTemplate ─────────────

    public function testGetPageTemplateUsesCorrectPath(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('getPageTemplate', ['token' => 'tok', 'id' => 9]);
        $this->assertGetTo('/page-template/9/');
    }

    public function testUpdatePageTemplateUsesPatch(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('updatePageTemplate', ['token' => 'tok', 'id' => 9, 'name' => 'Invoice Template']);
        $this->assertPatchTo('/page-template/9/');
        $this->assertBodyContains('name', 'Invoice Template');
    }

    public function testDeletePageTemplateReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePageTemplate', ['token' => 'tok', 'id' => 9]);
        $this->assertDeletedMessage($result);
    }

    // ── getAnniversaryMailing / updateAnniversaryMailing / deleteAnniversaryMailing

    public function testGetAnniversaryMailingUsesCorrectPath(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('getAnniversaryMailing', ['token' => 'tok', 'id' => 8]);
        $this->assertGetTo('/anniversary-mailing/8/');
    }

    public function testUpdateAnniversaryMailingUsesPatch(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('updateAnniversaryMailing', ['token' => 'tok', 'id' => 8, 'name' => 'Birthday Mail']);
        $this->assertPatchTo('/anniversary-mailing/8/');
        $this->assertBodyContains('name', 'Birthday Mail');
    }

    public function testDeleteAnniversaryMailingReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteAnniversaryMailing', ['token' => 'tok', 'id' => 8]);
        $this->assertDeletedMessage($result);
    }

    // ── getPublicChatRoom / updatePublicChatRoom / deletePublicChatRoom ────────

    public function testGetPublicChatRoomUsesCorrectPath(): void
    {
        $this->addOk('{"id":11}');
        $this->tools->dispatch('getPublicChatRoom', ['token' => 'tok', 'id' => 11]);
        $this->assertGetTo('/public-chat-room/11/');
    }

    public function testUpdatePublicChatRoomUsesPatch(): void
    {
        $this->addOk('{"id":11}');
        $this->tools->dispatch('updatePublicChatRoom', ['token' => 'tok', 'id' => 11, 'name' => 'Support']);
        $this->assertPatchTo('/public-chat-room/11/');
        $this->assertBodyContains('name', 'Support');
    }

    public function testDeletePublicChatRoomReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePublicChatRoom', ['token' => 'tok', 'id' => 11]);
        $this->assertDeletedMessage($result);
    }

    // ── getSmtpEmailSetting / updateSmtpEmailSetting / deleteSmtpEmailSetting ─

    public function testGetSmtpEmailSettingUsesCorrectPath(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getSmtpEmailSetting', ['token' => 'tok', 'id' => 1]);
        $this->assertGetTo('/smtp-email-setting/1/');
    }

    public function testUpdateSmtpEmailSettingUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateSmtpEmailSetting', ['token' => 'tok', 'id' => 1, 'name' => 'Main SMTP']);
        $this->assertPatchTo('/smtp-email-setting/1/');
        $this->assertBodyContains('name', 'Main SMTP');
    }

    public function testDeleteSmtpEmailSettingReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteSmtpEmailSetting', ['token' => 'tok', 'id' => 1]);
        $this->assertDeletedMessage($result);
    }

    // ── getChairmanLevel / updateChairmanLevel / deleteChairmanLevel ──────────

    public function testGetChairmanLevelUsesCorrectPath(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('getChairmanLevel', ['token' => 'tok', 'id' => 2]);
        $this->assertGetTo('/chairman-level/2/');
    }

    public function testUpdateChairmanLevelUsesPatch(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('updateChairmanLevel', ['token' => 'tok', 'id' => 2, 'name' => 'Treasurer']);
        $this->assertPatchTo('/chairman-level/2/');
        $this->assertBodyContains('name', 'Treasurer');
    }

    public function testDeleteChairmanLevelReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteChairmanLevel', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    // ── getVotingQuestion / updateVotingQuestion / deleteVotingQuestion ───────

    public function testGetVotingQuestionUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getVotingQuestion', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/voting-question/3/');
    }

    public function testUpdateVotingQuestionUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateVotingQuestion', ['token' => 'tok', 'id' => 3, 'question' => 'Are you sure?']);
        $this->assertPatchTo('/voting-question/3/');
        $this->assertBodyContains('question', 'Are you sure?');
    }

    public function testDeleteVotingQuestionReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteVotingQuestion', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── getChairmanNote / updateChairmanNote / deleteChairmanNote ─────────────

    public function testGetChairmanNoteUsesCorrectPath(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('getChairmanNote', ['token' => 'tok', 'id' => 4]);
        $this->assertGetTo('/chairman-note/4/');
    }

    public function testUpdateChairmanNoteUsesPatch(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('updateChairmanNote', ['token' => 'tok', 'id' => 4, 'text' => 'Remember deadline']);
        $this->assertPatchTo('/chairman-note/4/');
        $this->assertBodyContains('text', 'Remember deadline');
    }

    public function testDeleteChairmanNoteReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteChairmanNote', ['token' => 'tok', 'id' => 4]);
        $this->assertDeletedMessage($result);
    }

    // ── getOauth2CustomClaim / updateOauth2CustomClaim / deleteOauth2CustomClaim

    public function testGetOauth2CustomClaimUsesCorrectPath(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('getOauth2CustomClaim', ['token' => 'tok', 'id' => 5]);
        $this->assertGetTo('/oauth2-custom-claim/5/');
    }

    public function testUpdateOauth2CustomClaimUsesPatch(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('updateOauth2CustomClaim', ['token' => 'tok', 'id' => 5, 'claim_name' => 'roles']);
        $this->assertPatchTo('/oauth2-custom-claim/5/');
        $this->assertBodyContains('claim_name', 'roles');
    }

    public function testDeleteOauth2CustomClaimReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteOauth2CustomClaim', ['token' => 'tok', 'id' => 5]);
        $this->assertDeletedMessage($result);
    }

    // ── getOrganizationToken / updateOrganizationToken / deleteOrganizationToken

    public function testGetOrganizationTokenUsesCorrectPath(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('getOrganizationToken', ['token' => 'tok', 'id' => 6]);
        $this->assertGetTo('/organization-token/6/');
    }

    public function testUpdateOrganizationTokenUsesPatch(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('updateOrganizationToken', ['token' => 'tok', 'id' => 6, 'name' => 'API Key']);
        $this->assertPatchTo('/organization-token/6/');
        $this->assertBodyContains('name', 'API Key');
    }

    public function testDeleteOrganizationTokenReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteOrganizationToken', ['token' => 'tok', 'id' => 6]);
        $this->assertDeletedMessage($result);
    }

    // ── getOauth2Application / updateOauth2Application / deleteOauth2Application

    public function testGetOauth2ApplicationUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getOauth2Application', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/oauth2-application/7/');
    }

    public function testUpdateOauth2ApplicationUsesPatch(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('updateOauth2Application', ['token' => 'tok', 'id' => 7, 'name' => 'My App']);
        $this->assertPatchTo('/oauth2-application/7/');
        $this->assertBodyContains('name', 'My App');
    }

    public function testDeleteOauth2ApplicationReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteOauth2Application', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    // ── getOauthCredentials / updateOauthCredentials / deleteOauthCredentials ─

    public function testGetOauthCredentialsUsesCorrectPath(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('getOauthCredentials', ['token' => 'tok', 'id' => 8]);
        $this->assertGetTo('/oauth-credentials/8/');
    }

    public function testUpdateOauthCredentialsUsesPatch(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('updateOauthCredentials', ['token' => 'tok', 'id' => 8, 'name' => 'New Creds']);
        $this->assertPatchTo('/oauth-credentials/8/');
        $this->assertBodyContains('name', 'New Creds');
    }

    public function testDeleteOauthCredentialsReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteOauthCredentials', ['token' => 'tok', 'id' => 8]);
        $this->assertDeletedMessage($result);
    }

    // ── listChairmanTutorials / getChairmanTutorial / create / update / delete ─

    public function testListChairmanTutorialsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listChairmanTutorials', ['token' => 'tok']);
        $this->assertGetTo('/chairman-tutorial/');
    }

    public function testGetChairmanTutorialUsesCorrectPath(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getChairmanTutorial', ['token' => 'tok', 'id' => 1]);
        $this->assertGetTo('/chairman-tutorial/1/');
    }

    public function testCreateChairmanTutorialUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createChairmanTutorial', ['token' => 'tok', 'tutorial_members_seen' => true]);
        $this->assertPostTo('/chairman-tutorial/');
    }

    public function testUpdateChairmanTutorialUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateChairmanTutorial', ['token' => 'tok', 'id' => 1, 'tutorial_members_seen' => true]);
        $this->assertPatchTo('/chairman-tutorial/1/');
    }

    public function testDeleteChairmanTutorialReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteChairmanTutorial', ['token' => 'tok', 'id' => 1]);
        $this->assertDeletedMessage($result);
    }

    // ── getLsbSport / updateLsbSport / deleteLsbSport ─────────────────────────

    public function testGetLsbSportUsesCorrectPath(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('getLsbSport', ['token' => 'tok', 'id' => 2]);
        $this->assertGetTo('/lsb-sport/2/');
    }

    public function testUpdateLsbSportUsesPatch(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('updateLsbSport', ['token' => 'tok', 'id' => 2, 'title' => 'Swimming']);
        $this->assertPatchTo('/lsb-sport/2/');
        $this->assertBodyContains('title', 'Swimming');
    }

    public function testDeleteLsbSportReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteLsbSport', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    // ── getDosbSport / updateDosbSport / deleteDosbSport ─────────────────────

    public function testGetDosbSportUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getDosbSport', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/dosb-sport/3/');
    }

    public function testUpdateDosbSportUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateDosbSport', ['token' => 'tok', 'id' => 3, 'title' => 'Athletics']);
        $this->assertPatchTo('/dosb-sport/3/');
        $this->assertBodyContains('title', 'Athletics');
    }

    public function testDeleteDosbSportReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteDosbSport', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── getFileSystemPath / updateFileSystemPath / deleteFileSystemPath ────────

    public function testGetFileSystemPathUsesCorrectPath(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('getFileSystemPath', ['token' => 'tok', 'id' => 4]);
        $this->assertGetTo('/file-system-path/4/');
    }

    public function testUpdateFileSystemPathUsesPatch(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('updateFileSystemPath', ['token' => 'tok', 'id' => 4, 'name' => 'Documents']);
        $this->assertPatchTo('/file-system-path/4/');
        $this->assertBodyContains('name', 'Documents');
    }

    public function testDeleteFileSystemPathReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteFileSystemPath', ['token' => 'tok', 'id' => 4]);
        $this->assertDeletedMessage($result);
    }

    // ── listCommunityFunctionFeedbacks / get / create / delete ────────────────

    public function testListCommunityFunctionFeedbacksUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCommunityFunctionFeedbacks', ['token' => 'tok']);
        $this->assertGetTo('/community-function-feedback/');
    }

    public function testGetCommunityFunctionFeedbackUsesCorrectPath(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('getCommunityFunctionFeedback', ['token' => 'tok', 'id' => 5]);
        $this->assertGetTo('/community-function-feedback/5/');
    }

    public function testCreateCommunityFunctionFeedbackUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createCommunityFunctionFeedback', ['token' => 'tok', 'feedback_text' => 'Great feature!', 'rating' => 5]);
        $this->assertPostTo('/community-function-feedback/');
        $this->assertBodyContains('feedback_text', 'Great feature!');
    }

    public function testDeleteCommunityFunctionFeedbackReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteCommunityFunctionFeedback', ['token' => 'tok', 'id' => 5]);
        $this->assertDeletedMessage($result);
    }

    // ── listArticleObjects / get / create / update / delete ───────────────────

    public function testListArticleObjectsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listArticleObjects', ['token' => 'tok']);
        $this->assertGetTo('/article-object/');
    }

    public function testGetArticleObjectUsesCorrectPath(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('getArticleObject', ['token' => 'tok', 'id' => 6]);
        $this->assertGetTo('/article-object/6/');
    }

    public function testCreateArticleObjectUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createArticleObject', ['token' => 'tok', 'name' => 'Laptop']);
        $this->assertPostTo('/article-object/');
        $this->assertBodyContains('name', 'Laptop');
    }

    public function testUpdateArticleObjectUsesPatch(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('updateArticleObject', ['token' => 'tok', 'id' => 6, 'name' => 'Desktop']);
        $this->assertPatchTo('/article-object/6/');
        $this->assertBodyContains('name', 'Desktop');
    }

    public function testDeleteArticleObjectReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteArticleObject', ['token' => 'tok', 'id' => 6]);
        $this->assertDeletedMessage($result);
    }

    // ── listFeatureRequests / get / create / delete ───────────────────────────

    public function testListFeatureRequestsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listFeatureRequests', ['token' => 'tok']);
        $this->assertGetTo('/feature-request/');
    }

    public function testGetFeatureRequestUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getFeatureRequest', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/feature-request/7/');
    }

    public function testCreateFeatureRequestUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createFeatureRequest', ['token' => 'tok', 'title' => 'Dark Mode']);
        $this->assertPostTo('/feature-request/');
        $this->assertBodyContains('title', 'Dark Mode');
    }

    public function testDeleteFeatureRequestReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteFeatureRequest', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    // ── listUpdateHighlights / get / create / update / delete ─────────────────

    public function testListUpdateHighlightsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listUpdateHighlights', ['token' => 'tok']);
        $this->assertGetTo('/update-highlight/');
    }

    public function testGetUpdateHighlightUsesCorrectPath(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('getUpdateHighlight', ['token' => 'tok', 'id' => 8]);
        $this->assertGetTo('/update-highlight/8/');
    }

    public function testCreateUpdateHighlightUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createUpdateHighlight', ['token' => 'tok', 'title' => 'v3.0 Release', 'version' => '3.0']);
        $this->assertPostTo('/update-highlight/');
        $this->assertBodyContains('title', 'v3.0 Release');
    }

    public function testUpdateUpdateHighlightUsesPatch(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('updateUpdateHighlight', ['token' => 'tok', 'id' => 8, 'title' => 'v3.1 Release']);
        $this->assertPatchTo('/update-highlight/8/');
        $this->assertBodyContains('title', 'v3.1 Release');
    }

    public function testDeleteUpdateHighlightReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteUpdateHighlight', ['token' => 'tok', 'id' => 8]);
        $this->assertDeletedMessage($result);
    }

    // ── listUpdateHighlightEntries / get / create / update / delete ───────────

    public function testListUpdateHighlightEntriesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listUpdateHighlightEntries', ['token' => 'tok']);
        $this->assertGetTo('/update-highlight-entry/');
    }

    public function testGetUpdateHighlightEntryUsesCorrectPath(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('getUpdateHighlightEntry', ['token' => 'tok', 'id' => 9]);
        $this->assertGetTo('/update-highlight-entry/9/');
    }

    public function testCreateUpdateHighlightEntryUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createUpdateHighlightEntry', ['token' => 'tok', 'title' => 'New Feature', 'body' => 'Details']);
        $this->assertPostTo('/update-highlight-entry/');
        $this->assertBodyContains('title', 'New Feature');
    }

    public function testUpdateUpdateHighlightEntryUsesPatch(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('updateUpdateHighlightEntry', ['token' => 'tok', 'id' => 9, 'title' => 'Updated Feature']);
        $this->assertPatchTo('/update-highlight-entry/9/');
        $this->assertBodyContains('title', 'Updated Feature');
    }

    public function testDeleteUpdateHighlightEntryReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteUpdateHighlightEntry', ['token' => 'tok', 'id' => 9]);
        $this->assertDeletedMessage($result);
    }

    // ── normalize ─────────────────────────────────────────────────────────────

    public function testNormalizeUsesPost(): void
    {
        $this->addOk('{"status":"ok"}');
        $this->tools->dispatch('normalize', ['token' => 'tok', 'member' => 42]);
        $this->assertPostTo('/normalize/');
        $this->assertBodyContains('member', 42);
    }

    // ── updatePriceGroup ─────────────────────────────────────────────────────

    public function testUpdatePriceGroupUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updatePriceGroup', ['token' => 'tok', 'id' => 3, 'name' => 'Gold']);
        $this->assertPatchTo('/price-group/3/');
        $this->assertBodyContains('name', 'Gold');
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentMiscTool', ['token' => 'tok']);
    }
}
