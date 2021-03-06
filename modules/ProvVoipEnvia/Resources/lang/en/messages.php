<?php

/*
|--------------------------------------------------------------------------
| Language lines for module ProvVoipEnvia
|--------------------------------------------------------------------------
|
| The following language lines are used by the module ProvVoipEnvia
| As far as we know this module is in use in Germany, only. So no translation
| for other languages is needed at the moment.
|
 */

return [
    'aNotSetInB' => ':0 not set in :1',
    'activationDateNotSetForNumber' => 'No activation date set for number :0',
    'addFutureVoipItem' => 'maybe you have to create a VoIP item with future start date?',
    'allNumbersSameEkp' => 'All numbers to be created have to have the same incoming EKP code.',
    'allPortedOrNone' => 'Either all given numbers have to be ported or none – mixing is not allowed.',
    'apiVersionNotFloat' => 'PROVVOIPENVIA__REST_API_VERSION in .env has to be a float value (e.g.: 1.4)',
    'availableKeys' => 'Available keys',
    'back' => 'Bring me back…',
    'backTo' => 'Bring me back to :0',
    'carrierInvalid' => ':0 is not a valid carrier_code.',
    'carrierNoPortingIn' => 'If no incoming porting: Carriercode has to be D057 (envia TEL).',
    'changeInstallationAddressSucessful' => 'Installation address change successful (order ID: :0)',
    'changeMethodSucessful' => 'Method change successful (order ID: :0)',
    'changeTariffSucessful' => 'Tariff change successful (order ID: :0)',
    'changeVariationSucessful' => 'Variation change successful (order ID: :0)',
    'changingEnviaCustId' => 'Changing envia TEL customer reference from :0 to :1.',
    'chooseNumbersToCreate' => 'Please choose at least one phonenumber to be created with this contract',
    'clearedManagement' => 'Cleared data in phonenumbermanagement :0',
    'clearedModemContract' => 'Cleared data in modem :0 and contract :1.',
    'clearingDatabaseAfterOrderCancelNotImplemented' => 'Attention: Updating the database to reset related database entries for method :0 (on cancelled order :1) not yet implemented. Has to be done manually!!',
    'contractCreateDifferentCustomerIds' => 'Error in processing contract_create response (order ID: :0): Existing customer_external_id (:1) different from received one (:2)',
    'createContractWithNumbers' => 'Create contract with chosen numbers',
    'createdEnviaContract' => 'Created envia TEL contract :0',
    'createdPhonebookentry' => 'Created PhonebookEntry :0 at Telekom',
    'createdPhonebookentryLocal' => 'Created new PhonebookEntry for phonenumber :0 with data delivered by Telekom',
    'creating' => 'Creating',
    'creatingEnviaContract' => 'Creating envia TEL contract :0',
    'creatingEnviaOrder' => 'Creating envia TEL order :0',
    'custUpdatedByOrder' => 'Customer updated (envia TEL order ID: :0)',
    'customerIdDifferentOrderContract' => 'Error: Customer reference in order (:0) and contract (:1) are different!',
    'customernumberNotExisting' => 'Customernumber does not exist – try using legacy version.',
    'deacDateNotSet' => 'No deactivation date in PhonenumberManagement set.',
    'deletedOrder' => 'Order has been deleted in database',
    'deletedPhonebookentry' => 'Deleted PhonebookEntry :0 at Telekom',
    'deleting' => 'Deleting',
    'description' => 'Description',
    'differentActivationDates' => 'Given numbers have different activation dates (:0, :1)',
    'differentSubscriberData' => 'Differences in subscriber data (:0 != :1)',
    'doManuallyNow' => 'DO THIS MANUALLY NOW!',
    'documentAlreadyUploaded' => 'Given document has aleady been uploaded.',
    'documentNotFound' => 'ERROR: The file :path cannot be found!',
    'enviaContractDeletedByCron' => 'Deletion of envia TEL contracts will be done via cron job.',
    'enviaContractDifferentInOrderAndManagement' => 'PhonenumberManagement (:0): different enviacontract_id in order and management. Will not update since this id changes on contract/relocate. Will be handled in own method, can be set to current value using contract/get_reference',
    'enviaContractIdDifferentOrderModem' => 'Error: Contract reference in order (:0) and modem (:1) are different!',
    'enviaContractIdMissing' => 'No envia TEL contract ID (contract_external_id) found.',
    'enviaContractUpToDate' => 'Differences in subscriber data (:0 != :1)',
    'enviaCustIdIs' => 'envia TEL customer ID is :0.',
    'error' => 'Error!',
    'errorCreatingXml' => 'There was error creating XML to be sent to envia TEL',
    'followingErrorsOccured' => 'The following error(s) occured',
    'freeNumbers' => 'Free numbers',
    'hasToBeNumeric' => ':value has to be numeric',
    'informationalOnly' => 'Informational data only.',
    'invalidLinesInCsv' => 'There are invalid lines in returned CSV:',
    'itemChangeTariff' => 'ATTENTION: You have to “Change purchase tariff” (envia TEL API), too!',
    'itemChangeVariation' => 'ATTENTION: You have to “Change tariff” (envia TEL API), too!',
    'jobCurrentlyNotAllowed' => 'Job :0 is currently not allowed',
    'legacyCustomernumberNotExisting' => 'Legacy customernumber does not exist – try using normal version.',
    'mgcpNotImplemented' => 'MGCP is not implemented.',
    'miscGetKeysUnused' => 'Attention: ATM the following data is not used to update database/files!',
    'miscGetKeysWarning' => 'Attention: Data for this keys should be downloaded max. once per day.',
    'modemStillNumbersAttached' => 'There are still phonenumbers attached to :href! Don\'t forget to move them, too: :numbers',
    'multipleEnviaContractsAtModem' => 'There is more than one envia TEL contract used on this modem (:0). Processing this is not yet implemented – please use the envia TEL Web API.',
    'needsToBeStringOrArray' => ':0 needs to be string or array, :1 given.',
    'noContractForOrder' => 'No contract found for envia TEL contract :0. Skipping.',
    'noContractIdGiven' => 'Warning: No contract_id given',
    'noModelGiven' => 'No model given',
    'noModemForOrder' => 'No modem found for envia TEL contract :0. Skipping.',
    'noModemIdGiven' => 'Warning: No modem_id given',
    'noNumbersForEnviaContract' => 'No phonenumbers found for envia TEL contract :0.',
    'noPhonenumberIdGiven' => 'Warning: No phonenumber_id given',
    'noPhonenumberOrManagamentIdGiven' => 'Warning: Neither phonenumber_id nor phonenumbermanagement_id given',
    'noTrcSet' => 'TRCclass not set.',
    'no_management' => 'No PhonenumberManagement',
    'orderCancelFailed' => 'Cancelation failed. Restored order with id :0 (envia TEL ID :1)',
    'orderCancelSuccess' => 'Cancelation of order :0 (envia TEL ID :1) was successful.',
    'orderCannotCreateIdMissing' => 'Cannot create EnviaOrder – neither contract_id nor modem_id nor phonenumbermanagement_id given.',
    'orderCannotCreateModelMissing' => 'Cannot create EnviaOrder – :model :id does not exist',
    'orderCheckInteraction' => 'Please check if user interaction is necessary.',
    'orderDeletedByCron' => 'Deletion of envia TEL orders will be done via cron job.',
    'orderGetStatusError' => 'Error (HTTP status is :0)',
    'orderGetStatusSuccess' => 'Success (HTTP status is :0)',
    'orderHasBeenUpdated' => 'Order has been updated!',
    'orderIdOrderDocumentMismatch' => 'Given order_id (:0) not correct for given enviaorderdocument.',
    'orderNotExisting' => 'Order :0 does not exist:',
    'orderNotExistingError' => 'ERROR: There is no order with order_id :0 in table enviaorders',
    'orderRelatedToContract' => 'envia TEL order seems to be contract related',
    'orderRelatedToCustomer' => 'envia TEL order seems to be customer related',
    'orderRelatedToEnviaContract' => 'Order is related to envia TEL contract :0 (:1) – Updating relation.',
    'orderRelatedToNothing' => 'envia TEL order seems to be standalone – no relation found',
    'orderRelatedToPhonenumber' => 'envia TEL order seems to be phonenumber related',
    'orderStateCur' => 'Status for envia TEL order :0',
    'phonebookentryNotCreatedNoMgmt' =>  'Cannot create phonebookentry – phonenumbermanagement ID missing or phonenumbermanagement not found',
    'phonenumberContractRefChanged' => 'Stored envia TEL contract reference in :0 (:1) does not match returned value :2. Overwriting.',
    'phonenumberContractRefIs' => 'envia TEL contract reference for phonenumber :0 is :1',
    'phonenumberContractRefNew' => 'envia TEL contract reference not set at phonenumber :0 – set to :1',
    'phonenumberHasNoManagement' => 'Chosen phonenumber :0 has no management.',
    'phonenumberInCsvManyExisting' => 'Error processing get_orders_csv_response: Phonenumber :0/:1 exists :2 times. Clean your database! Skipping order :3',
    'phonenumberInCsvNotExisting' => 'Error processing get_orders_csv_response: Phonenumber :0/:1 does not exist. Skipping order :2',
    'phonenumberManagementNATrc' => 'No phonenumbermanagement found for phonenumber :0. Cannot set TRC class.',
    'phonenumberNA' => 'Phonenumber :0 does not exist in our database!',
    'phonenumberNeededToCreateContract' => 'Can only create contract with at least one phonenumber, but none given',
    'phonenumberNotBelongsToModem' => 'Phonenumber :0 does not belong to modem',
    'phonenumberNotCreatedAtEnviaNoModemChange' => 'Number has not been created at envia TEL – will not change any modem data.',
    'phonenumbermanagementNACreateNew' => 'No phonenumbermanagement found for phonenumber :0. Creating new one – you have to set some values manually!',
    'phonenumbermanagementNAInactiveNumber' => 'No phonenumbermanagement found for phonenumber :0. Will not create one because number is inactive!',
    'phonenumbermanagementNotDeletableReasonActiveEnviaContract' => ': There is an active or pending envia TEL contract.',
    'phonenumbermanagementNotDeletableReasonEnviaContractEndDate' => ': End date of terminated envia TEL contract not reached yet.',
    'phonenumbermanagementNotDeletable' => 'Cannot delete PhonenumberManagement :0',
    'phonenumbermanagementNotDeletableReasonPhonebookentry' => ': There is a phonebookentry which has to be deleted (and possibly canceled) first.',
    'phonenumbermanagementRemovedEnviaRef' =>  'Removed envia TEL contract reference. This can be restored via „Get envia TEL contract reference“.',
    'phonenumbersBelongToDifferentModems' => 'Phonenumbers related to envia TEL contract :0 do belong to different modems. Skipping.',
    'pingError' => 'Something went wrong.',
    'pingSuccess' => 'All works fine.',
    'relationOrderPhonenumberAdded' => 'Added relation between existing enviaorder :0 and phonenumber :1',
    'relocateDateMissing' => 'Date of installation address change has to be set.',
    'removedSipDomainFromPhonenumber' => 'SIP domain at phonenumber :0 deleted (will be set by envia TEL)',
    'removedSipUsernameFromPhonenumber' => 'SIP username at phonenumber :0 deleted (will be set by envia TEL)',
    'requestEnvia' => 'Request envia TEL API',
    'sendToEnviaCancel' => 'NOOO! Please bring me back…',
    'sendToEnviaHead1' => 'Data to be sent to envia TEL',
    'sendToEnviaHead2' => 'You are going to change data at envia TEL! Proceed?',
    'sendToEnviaNow' => 'Yes, send data now!',
    'settingExternalCustId' => 'Setting external customer id for contract :0 to :1.',
    'sipChanged' => 'Changed SIP data for phonenumber :0.',
    'sipDateNotChangedAutomaticallyAtEnvia' => 'Autochanging of SIP data at envia TEL is not implemented yet.<br>You have to :href',
    'todoCheck' => 'TODO: check if we have to do something…',
    'trcChanged' => 'Changed TRC class for phonenumber :0.',
    'updated' => 'Updated :0',
    'updatedContract' => 'Updated contract',
    'updatedCustExtId' => 'envia TEL customer id at contract set to :0',
    'updatedManagement' => 'Updated PhonenumberManagement :0',
    'updatedModem' => 'Updated modem',
    'updatedOrder' => 'Updated envia TEL order :0',
    'updatedPhonebookentry' => 'Updated PhonebookEntry :0 at Telekom',
    'updatedPhonebookentryLocal' => 'Updated new PhonebookEntry for phonenumber :0 with data delivered by Telekom',
    'updatedVoipAccount' => 'Updated VoIP account (order ID :0)',
    'updating' => 'Updating',
    'updatingEnviaContract' => 'Updating envia TEL contract :0',
    'updatingEnviaCustInEnviaContract' => 'Updating envia_customer_reference in enviacontract :0 to :1',
    'uploadedFile' => 'File uploaded successfully.',
    'usageData' => 'Usage data',
    'usingFilter' => 'using filter',
    'valueNotAllowedForParam' => 'Value :a not allowed for param $level',
    'voiceData' => 'Voice data for modem :0',
];
