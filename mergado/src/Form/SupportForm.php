<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Form;

use Mergado\Query\SupportPageQuery;
use Mergado\Service\LogService;
use Mergado\Utility\TemplateLoader;

class SupportForm
{
    /**
     * @var LogService
     */
    private $logger;

    public function __construct()
    {
        $this->logger = LogService::getInstance();
    }

    public function sendEmailIfSubmitted($module): bool
    {
        if (isset($_POST['submit-ticket-form']) && $_GET['page'] === 'support') {
            $to = 'prestashop@mergado.cz';
            $from = $_POST['email'];
            $subject = '[MP_support] ' . $_POST['subject'];

            $settingsData = SupportPageQuery::getInformationForSupport($module);

            $html = TemplateLoader::getTemplate(__MERGADO_DIR__ . 'views/templates/mail/supportMail.php', [
                'issue' => $_POST['issue'],
                'settingsData' => $settingsData
            ]);

            $this->sendMail($this->logger->getLogDir(), $html, $subject, $to, $from);

            unset($_POST['email'], $_POST['subject'], $_POST['issue']);

            return true;
        }

        return false;
    }

    private function sendMail($fileAttachment, string $message, string $subject, string $to, string $from): bool
    {
        $fromName = '';
        $file = $fileAttachment;
        $htmlContent = $message;
        $headers = "From: $fromName" . " <" . $from . ">";
        $semi_rand = md5((string)time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
        $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";

        if ((!empty($file)) > 0) {
            if (is_file($file)) {
                $message .= "--{$mime_boundary}\n";
                $fp = @fopen($file, "rb");
                $data = @fread($fp, filesize($file));

                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .= "Content-Type: application/octet-stream; name=\"" . basename($file) . "\"\n" .
                    "Content-Description: " . basename($file) . "\n" .
                    "Content-Disposition: attachment;\n" . " filename=\"" . basename($file) . "\"; size=" . filesize($file) . ";\n" .
                    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
        }
        $message .= "--{$mime_boundary}--";
        $returnpath = "-f" . $from;

        return @mail($to, $subject, $message, $headers, $returnpath);
    }
}
