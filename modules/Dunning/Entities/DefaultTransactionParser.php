<?php

namespace Modules\Dunning\Entities;

use ChannelLog;
use Illuminate\Support\Str;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaMandate;

class DefaultTransactionParser
{
    protected $conf;
    public $excludeRegexesRelPath = 'config/dunning/transferExcludes.php';
    protected $excludeRegexes;

    /**
     * Designators in transfer reason and it's corresponding variable names for the mandatory entries
     * = Bezeichner
     *
     * @var array
     */
    public static $designators = [
        'ABWA+' => '',              // Abweichender SEPA Auftraggeber
        'ABWE+' => '',              // Abweichender SEPA Empfänger
        'BIC+' => '',               // SEPA BIC Auftraggeber
        'BREF+' => '',              // Bankreferenz, Instruction ID
        'COAM+' => 'bank_fee',      // Zinskompensationsbetrag
        'CRED+' => '',              // SEPA Creditor Identifier
        'DEBT+' => '',              // Originator Identifier
        'IBAN+' => '',              // SEPA IBAN Auftraggeber
        'EREF+' => 'invoiceNr',     // SEPA End to End-Referenz
        'KREF+' => '',              // Kundenreferenz
        'MREF+' => 'mref',          // SEPA Mandatsreferenz
        'OAMT+' => 'amount',        // Ursprünglicher Umsatzbetrag
        'RREF+' => '',              // Retourenreferenz
        'SVWZ+' => '',              // SEPA Verwendungszweck
// TODO: Move to specific TransactionParser
        'PURP+' => '',              // Volksbank Purpose ?
    ];

    /**
     * Parse a transaction
     *
     * @return obj  Debt
     */
    public function parse(\Kingsquare\Banking\Transaction $transaction)
    {
        if ($transaction->getDebitCredit() == 'D') {
            $debt = $this->parseDebit($transaction);
            $this->addFee($debt);
        } else {
            $debt = $this->parseCredit($transaction);
        }

        if ($debt) {
            $debt->date = $transaction->getValueTimestamp('Y-m-d H:i:s');
        }

        return $debt;
    }

    /**
     * Parse a debit transaction
     *
     * @return obj  Debt or null
     */
    private function parseDebit($transaction)
    {
        $debt = new Debt;
        $description = [];
        $holder = $iban = $invoiceNr = $mref = '';
        $amount = $bank_fee = 0;
        $descriptionArray = explode('?', $transaction->getDescription());

        if ($this->discardDebitTransactionType($descriptionArray[0])) {
            return;
        }

        foreach ($descriptionArray as $line) {
            $key = substr($line, 0, 2);
            $line = substr($line, 2);

            // 20  to 29
            if (preg_match('/^2[0-9]/', $key)) {
                $ret = $this->getVarFromDesignator($line);

                if ($ret['varName'] == 'description') {
                    $description[] = $ret['value'];
                } else {
                    $varName = $ret['varName'];
                    $$varName = $ret['value'];
                }
            }

            // if (Str::startsWith($line, '31')) {
            if ($key == '31') {
                $iban = utf8_encode($line);
                continue;
            }

            if (in_array($key, ['32', '33'])) {
                $holder = utf8_encode($line);
                continue;
            }

            // 60 to 63
            if (preg_match('/^6[0-3]/', $key)) {
                $description[] = $line;

                continue;
            }
        }

        $logmsg = trans('dunning::messages.transaction.default.debit', [
            'holder' => $holder,
            'invoiceNr' => $invoiceNr,
            'mref' => $mref,
            'price' => number_format_lang($transaction->getPrice()),
            'iban' => $iban,
            ]);

        $success = $this->setDebitDebtRelations($debt, $invoiceNr, $mref, $iban, $logmsg);

        if ($success === false) {
            return;
        }

        $debt->amount = $amount;
        $debt->bank_fee = $bank_fee;
        $debt->description = substr(utf8_encode(implode('', $description)), 0, 255);

        ChannelLog::debug('dunning', trans('dunning::messages.transaction.create')." $logmsg");

        return $debt;
    }

    /**
     * Determine whether a debit transaction should be discarded dependent of transaction code or GVC (Geschäftsvorfallcode)
     * See https://www.hettwer-beratung.de/sepa-spezialwissen/sepa-technische-anforderungen/sepa-gesch%C3%A4ftsvorfallcodes-gvc-mt-940/
     * These transactions can never belong to NMSPrime
     *
     * @param  string
     * @return bool
     */
    private function discardDebitTransactionType($code)
    {
        // ABSCHLUSS or SEPA-Überweisung
        $codesToDiscard = ['177', '805'];

        if (in_array($code, $codesToDiscard)) {
            return true;
        }

        return false;
    }

    /**
     * Parse a Transaction description line for (1) mandatory informations and (2) transfer reason
     *
     * @param  string   line of transfer reason without beginning number
     * @return array
     */
    private function getVarFromDesignator($line)
    {
        if (! Str::startsWith($line, array_keys(self::$designators))) {
            // Descriptions without designator
            return ['varName' => 'description', 'value' => $line];
        }

        foreach (self::$designators as $key => $varName) {
            // Descriptions with designator
            if (Str::startsWith($line, $key) && ! $varName) {
                return ['varName' => 'description', 'value' => str_replace($key, '', $line)];
            }

            // Mandatory variables
            if (Str::startsWith($line, $key) && $varName) {
                if (in_array($key, ['COAM+', 'OAMT+'])) {
                    // Get fee and amount
                    $value = trim(str_replace($key, '', $line));
                    $value = str_replace(',', '.', $value);
                } else {
                    // Get mref and invoice nr
                    if ($key == 'EREF+') {
                        $key .= 'RG ';
                    }

                    $value = utf8_encode(trim(str_replace($key, '', $line)));
                }

                return ['varName' => $varName, 'value' => $value];
            }
        }
    }

    /**
     * Check if there's no mismatch in relation of contract to sepamandate or invoice
     * Set relation IDs on debt object if all is correct
     *
     * @return bool     true on success, false on mismatch
     */
    private function setDebitDebtRelations($debt, $invoiceNr, $mref, $iban, $logmsg)
    {
        // Get SepaMandate by iban & mref
        $sepamandate = SepaMandate::withTrashed()
            ->where('reference', $mref)->where('iban', $iban)
            ->orderBy('deleted_at')->orderBy('valid_from', 'desc')->first();

        if ($sepamandate) {
            $debt->sepamandate_id = $sepamandate->id;
        }

        // Get Invoice
        $invoice = Invoice::where('number', $invoiceNr)->where('type', 'Invoice')->first();

        if ($invoice) {
            $debt->invoice_id = $invoice->id;

            // Check if Transaction refers to same Contract via SepaMandate and Invoice
            if ($sepamandate && $sepamandate->contract_id != $invoice->contract_id) {
                // As debits are structured by NMSPrime in sepa xml - this is actually an error and should not happen
                ChannelLog::warning('dunning', trans('view.Discard')." $logmsg. ".trans('dunning::messages.transaction.debit.diffContractSepa'));

                return false;
            }

            // Assign Debt by invoice number (or invoice number and SepaMandate)
            $debt->contract_id = $invoice->contract_id;
        } else {
            if (! $sepamandate) {
                ChannelLog::info('dunning', trans('view.Discard')." $logmsg. ".trans('dunning::messages.transaction.debit.missSepaInvoice'));

                return false;
            }

            // Assign Debt by sepamandate
            $debt->contract_id = $sepamandate->contract_id;
        }

        return true;
    }

    private function parseCredit($transaction)
    {
        $debt = new Debt;
        $descriptionArray = explode('?', $transaction->getDescription());
        $reason = $holder = [];
        $iban = '';

        foreach ($descriptionArray as $key => $line) {
            // Transfer reason is 20 to 29
            if (preg_match('/^2[0-9]/', $line)) {
                $line = substr($line, 2);

                if (Str::startsWith($line, 'EREF+')) {
                    $invoiceNr = trim(str_replace('EREF+', '', $line));
                    continue;
                }

                if (Str::startsWith($line, 'MREF+')) {
                    $mref = trim(str_replace('MREF+', '', $line));
                    continue;
                }

                $reason[] = str_replace(array_keys(self::$designators), '', $line);
                continue;
            }

            // IBAN is usually not existent in the DB for credits - we could still try to check as it would find the customer in at least some cases
            if (Str::startsWith($line, '31')) {
                $iban = utf8_encode(substr($line, 2));
                continue;
            }

            if (Str::startsWith($line, ['32', '33'])) {
                $holder[] = substr($line, 2);
                continue;
            }
        }

        $holder = utf8_encode(implode('', $holder));
        $reason = utf8_encode(trim(implode('', $reason)));
        $price = number_format_lang($transaction->getPrice());
        $logmsg = trans('dunning::messages.transaction.default.credit', ['holder' => $holder, 'price' => $price, 'iban' => $iban, 'reason' => $reason]);

        $numbers = $this->searchNumbers($reason);

        if ($numbers['exclude']) {
            ChannelLog::info('dunning', trans('view.Discard')." $logmsg. ".trans('dunning::messages.transaction.credit.missInvoice'));

            return;
        }

        $numbers['eref'] = $invoiceNr ?? null;
        $numbers['mref'] = $mref ?? null;

        $success = $this->setCreditDebtRelations($debt, $numbers, $iban, $transaction, $logmsg);

        if ($success === false) {
            return;
        }

        $debt->amount = -1 * $transaction->getPrice();
        $debt->description = $reason;

        ChannelLog::debug('dunning', trans('dunning::messages.transaction.create')." $logmsg");

        return $debt;
    }

    /**
     * Set contract_id of debt only if a correct invoice number is given in the transfer reason
     *
     * NOTE: Invoice number is mandatory as transaction could otherwise have a totally different intention
     *  e.g. like costs for electrician or sth else not handled by NMSPrime
     *
     * @return bool     true on success, false otherwise
     */
    private function setCreditDebtRelations($debt, $numbers, $iban, $transaction, $logmsg)
    {
        $invoiceNr = $numbers['invoiceNr'] ?: $numbers['eref'];
        $invoice = Invoice::where('number', $invoiceNr)->where('type', 'Invoice')->first();

        if ($invoice) {
            $debt->contract_id = $invoice->contract_id;
            $debt->invoice_id = $invoice->id;

            return true;
        }

        $logmsg = trans('view.Discard')." $logmsg.";
        $hint = '';

        if ($invoiceNr) {
            $logmsg .= ' '.trans('dunning::messages.transaction.credit.noInvoice.notFound', ['number' => $invoiceNr]);
        } else {
            $logmsg .= ' '.trans('dunning::messages.transaction.credit.noInvoice.default');
        }

        // Give hints to what contract the transaction could be assigned
        // Or still add debt if it's almost secure that the transaction belongs to the customer and NMSPrime
        if ($numbers['contractNr']) {
            $contracts = Contract::where('number', 'like', '%'.$numbers['contractNr'].'%')->get();

            if ($contracts->count() > 1) {
                // As the prefix und suffix of the contract number is not considered it's possible to finde multiple contracts
                // When that happens we need to take this into consideration
                ChannelLog::error('dunning', trans('dunning::messages.transaction.credit.multipleContracts'));
            } elseif ($contracts->count() == 1) {
                // Create debt only if contract number is found and amount is same like in last invoice
                $ret = $this->addDebtBySpecialMatch($debt, $contracts->first(), $transaction);

                if ($ret) {
                    return true;
                }

                $hint .= ' '.trans('dunning::messages.transaction.credit.noInvoice.contract', ['contract' => $numbers['contractNr']]);
            }
        }

        $sepamandate = SepaMandate::where('iban', $iban)
            ->orderBy('valid_from', 'desc')
            ->where('valid_from', '<=', $transaction->getValueTimestamp('Y-m-d'))
            ->where(whereLaterOrEqual('valid_to', $transaction->getValueTimestamp('Y-m-d')))
            ->with('contract')
            ->first();

        if ($sepamandate) {
            // Create debt only if contract number is found and amount is same like in last invoice
            $ret = $this->addDebtBySpecialMatch($debt, $sepamandate->contract, $transaction);

            if ($ret) {
                return true;
            }

            $hint .= ' '.trans('dunning::messages.transaction.credit.noInvoice.sepa', ['contract' => $sepamandate->contract->number]);
        }

        if ($hint) {
            ChannelLog::notice('dunning', $logmsg.$hint);
        } else {
            ChannelLog::info('dunning', $logmsg);
        }

        return false;
    }

    /**
     * Add debt even if reference to invoice can not be established in the special cases of
     *  (1) found contract number
     *  (2) found sepa mandate
     *  AND corresponding invoice amount is the same as the amount of the transaction
     *
     * @return bool
     */
    private function addDebtBySpecialMatch($debt, $contract, $transaction)
    {
        if (is_null($this->conf)) {
            $this->getConf();
        }

        $invoice = Invoice::where('contract_id', $contract->id)
            ->where('type', 'Invoice')
            ->where('created_at', '<', $transaction->getValueTimestamp('Y-m-d'))
            ->orderBy('created_at', 'desc')->first();

        if (! $invoice || (round($invoice->charge * (1 + $this->conf['tax'] / 100), 2) != $transaction->getPrice())) {
            return false;
        }

        $debt->contract_id = $contract->id;
        // Collect debts added in special case and show only one log message at end
        $debt->addedBySpecialMatch = true;

        return true;
    }

    private function addFee($debt)
    {
        if (! $debt) {
            return;
        }

        // lazy loading of global dunning conf
        if (is_null($this->conf)) {
            $this->getConf();
        }

        $debt->total_fee = $debt->bank_fee;
        if ($this->conf['fee']) {
            $debt->total_fee = $this->conf['total'] ? $this->conf['fee'] : $debt->bank_fee + $this->conf['fee'];
        }
    }

    /**
     * Load dunning model and store relevant config in global variable
     */
    private function getConf()
    {
        $dunningConf = Dunning::first();
        $billingConf = BillingBase::first();

        $this->conf = [
            'fee' => $dunningConf->fee,
            'total' => $dunningConf->total,
            'tax' => $billingConf->tax,
        ];
    }

    /**
     * Search for contract and invoice number in credit transfer reason to assign debt to appropriate customer
     *
     * @param string
     * @return array
     */
    private function searchNumbers($transferReason)
    {
        // Match examples: Rechnungsnummer|Rechnungsnr|RE.-NR.|RG.-NR.|RG 2018/3/48616
        // preg_match('/R(.*?)((n(.*?)r)|G)(.*?)(\d{4}\/\d+\/\d+)/i', $transferReason, $matchInvoice);
        // $invoiceNr = $matchInvoice ? $matchInvoice[6] : '';

        // Match invoice numbers in NMSPrime default format: Year/CostCenter-ID/incrementing number - examples 2018/3/48616, 2019/15/201
        preg_match('/2\d{3}\/\d+\/\d+/i', $transferReason, $matchInvoice);
        $invoiceNr = $matchInvoice ? $matchInvoice[0] : '';

        // Match examples: Kundennummer|Kd-Nr|Kd.nr.|Kd.-Nr.|Kn|Knr 13451
        preg_match('/K(.*?)[ndr](.*?)([1-7]\d{1,4})/i', $transferReason, $matchContract);
        $contractNr = $matchContract ? $matchContract[3] : 0;

        // Special invoice numbers that ensure that transaction definitely doesn't belong to NMSPrime
        if (is_null($this->excludeRegexes)) {
            $this->getExcludeRegexes();
        }

        $exclude = '';
        foreach ($this->excludeRegexes as $regex => $group) {
            preg_match($regex, $transferReason, $matchInvoiceSpecial);

            if ($matchInvoiceSpecial) {
                $exclude = $matchInvoiceSpecial[$group];

                break;
            }
        }

        return [
            'contractNr' => $contractNr,
            'invoiceNr' => $invoiceNr,
            'exclude' => $exclude,
        ];
    }

    private function getExcludeRegexes()
    {
        if (\Storage::exists($this->excludeRegexesRelPath)) {
            $this->excludeRegexes = include storage_path("app/$this->excludeRegexesRelPath");
        }

        if (! $this->excludeRegexes) {
            $this->excludeRegexes = [];
        }
    }
}
