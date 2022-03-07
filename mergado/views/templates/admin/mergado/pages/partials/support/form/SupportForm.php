<?php

namespace Mergado\Forms;

use Mergado\Tools\LogClass;
use SupportClass;

class SupportForm
{
    public function sendEmailIfSubmited($shopId, $module)
    {
        if (isset($_POST['submit-ticket-form']) && $_GET['page'] === 'support') {
            $to = 'prestashop@mergado.cz';
            $from = $_POST['email'];
//            $name = (new ConfigurationCore())->get('PS_SHOP_NAME');
            $subject = '[MP_support] ' . $_POST['subject'];
            $msg = $_POST['issue'];

            $settingsData = SupportClass::getInformationsForSupport($shopId, $module);

            $msg .= '<br><br>';

            $msg .= '<table><tbody><tr><td>';
            $msg .= $this->formattedTable($settingsData['base']);
            $msg .= '</td>';

            $msg .= '<td>';
            $msg .= $this->formatAds($settingsData['adsystems']);
            $msg .= '</td></tr></tbody>';

            $msg .= $this->emailStyles();

            self::sendMail(LogClass::getLogDir(), $msg, $subject, $to, $from);

            unset($_POST['email']);
            unset($_POST['subject']);
            unset($_POST['issue']);

            return true;
        }

        return false;
    }

    private function formattedTable($data)
    {
        $output = '<table class="special">';
        $output .= '<thead>';
        $output .= '<tr><th colspan="2">Basic info</th></tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        foreach ($data as $item) {
            $output .= '<tr>';
            $output .= '<th>' . $item['name'] . '</th>';
            $output .= '<td>' . $item['value'] . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }

    private function formatAds($data)
    {
        $output = '<table class="special">';
        $output .= '<thead>';
        $output .= '<tr><th colspan="2">Ad systems</th></tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        foreach ($data as $key => $item) {
            if ($item === 'active') {
                $class = 'active';
            } else {
                $class = '';
            }

            $output .= '<tr class="' . $class . '">';
            $output .= '<th>' . $key . '</th>';
            $output .= '<td>' . $item . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';
        return $output;
    }

    private function emailStyles()
    {
        return '<style>
            table td {
                vertical-align: top;
            }

            table.special {
              font-family: Arial, Helvetica, sans-serif;
              border-collapse: collapse;
              font-size: 12px;
            }
            
            table.special td, table th {
              border: 1px solid #ddd;
              padding: 6px;
            }

            table.special tr:nth-child(even){background-color: #f2f2f2;}

            table.special tr:hover {background-color: #ddd;}

            table.special thead th {
              padding-top: 8px;
              padding-bottom: 8px;
              text-align: left;
              background-color: #04AA6D;
              color: white;
              font-weight: 600;
            }
           
            tr.active td,
            tr.active th {
                background-color: #e4ffc3;
            }
            
            table.special tbody th {
                font-weight: 500;
                text-align: left;
            }
        </style>';
    }

    private function sendMail($fileAttachment, $message, $subject, $to, $from)
    {
        $fromName = '';
        $file = $fileAttachment;
        $htmlContent = $message;
        $headers = "From: $fromName" . " <" . $from . ">";
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
        $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";

        if (!empty($file) > 0) {
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


