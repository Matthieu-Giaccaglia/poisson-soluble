<?php

namespace App\Controller;

use App\Service\HelperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SmsService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;

class SmsController extends AbstractController
{
    private SmsService $smsService;
    private Connection $db;
    private HelperService $helper;

    public function __construct(
        SmsService $smsService,
        Connection $connection,
        HelperService $helper
    ) {
        $this->smsService = $smsService;
        $this->db = $connection;
        $this->helper = $helper;
    }

    #[Route('alerter', name: 'sms_alerter', methods: ['GET'])]
    public function alerter(): Response
    {

        $insee = $_GET['insee'] ?? null;
        $message = $_GET['message'] ?? null;

        if (is_null($insee)) {
            return new JsonResponse(['success' => false, 'error' => "Missing insee parameter in url"], 422);
        }

        if (!$this->helper->isValidInsee($insee)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid Insee'], 422);
        }

        if (is_null($message)) {
            return new JsonResponse(['success' => false, 'error' => "Missing message parameter in url"], 422);
        }

        $recipients = $this->db->createQueryBuilder()
            ->select('*')
            ->from('recipient')
            ->where('insee = ?')
            ->setParameter(0, $insee)
            ->fetchAllAssociative();

        foreach ($recipients as $recipient) {
            $this->smsService->sendSms($recipient['telephone'], $message);
        }

        return new JsonResponse(['success' => true, 'result' => 'Message send !']);
    }
}
