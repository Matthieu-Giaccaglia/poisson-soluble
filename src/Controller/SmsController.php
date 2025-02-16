<?php

namespace App\Controller;

use App\Message\SmsMessage;
use App\Service\HelperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;

class SmsController extends AbstractController implements ApiKeyRequiredController
{
    private Connection $db;
    private HelperService $helper;
    private LoggerInterface $logger;

    public function __construct(Connection $connection, HelperService $helper, LoggerInterface $logger)
    {
        $this->db = $connection;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    #[Route('api/alerter', name: 'sms_alerter', methods: ['GET'])]
    public function alerter(MessageBusInterface $bus): Response
    {

        $this->logger->info('API alerter used.');

        $insee = $_GET['insee'] ?? null;
        $message = $_GET['message'] ?? null;

        if (is_null($insee)) {
            return new JsonResponse(['success' => false, 'error' => "Missing insee parameter in url"], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$this->helper->isValidInsee($insee)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid Insee'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (is_null($message)) {
            return new JsonResponse(['success' => false, 'error' => "Missing message parameter in url"], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $recipients = $this->db->createQueryBuilder()
            ->select('*')
            ->from('recipient')
            ->where('insee = ?')
            ->setParameter(0, $insee)
            ->fetchAllAssociative();

        foreach ($recipients as $recipient) {
            $bus->dispatch(new SmsMessage($recipient['telephone'], $message));
        }

        //
        return new JsonResponse(['success' => true, 'result' => 'Message send !']);
    }
}
