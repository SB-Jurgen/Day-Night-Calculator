<?php
namespace App\Controller;

use App\Form\Time;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DayNightController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('day_night/index.html.twig', [
            'form' => $this->createForm(Time::class),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/', name: 'calculate', methods: ['POST'])]
    public function calculateDayNightHours(Request $request): Response
    {
        $form = $this->createForm(Time::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $startTime = $data['start_time'];
            $endTime = $data['end_time'];
            $result = $this->getDayNightHours($data['start_time'], $data['end_time']);

            return $this->redirectToRoute('success', $result);
        }

        return $this->render('day_night/index.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/success', name: 'success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $dayHours = $request->query->get('dayHours');
        $nightHours = $request->query->get('nightHours');
        if (!isset($dayHours, $nightHours) || '' === $dayHours || '' === $nightHours) {
            return $this->redirectToRoute('index', ['error' => 'Missing input']);
        }

        return $this->render('day_night/success.html.twig', [
            'day_hours' => $dayHours,
            'night_hours' => $nightHours,
        ]);
    }

    /**
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @return float[]|int[]
     */
    private function getDayNightHours(DateTime $startTime, DateTime $endTime): array
    {
        $now = new DateTime();
        $startTime->setDate($now->format('Y'), $now->format('m'), $now->format('d'));
        $endTime->setDate($now->format('Y'), $now->format('m'), $now->format('d'));
        $dayStart = DateTime::createFromFormat('H:i', '06:00', new DateTimeZone('Europe/Tallinn'));
        $dayEnd = DateTime::createFromFormat('H:i', '22:00', new DateTimeZone('Europe/Tallinn'));

        if ($endTime <= $startTime) {
            $endTime->modify('+1 day');
        }

        $dayHours = 0;
        $nightHours = 0;

        $current = clone $startTime;
        while ($current < $endTime) {
            $currentDayStart = (clone $dayStart)->setDate($current->format('Y'), $current->format('m'), $current->format('d'));
            $currentDayEnd = (clone $dayEnd)->setDate($current->format('Y'), $current->format('m'), $current->format('d'));

            if ($current >= $currentDayStart && $current < $currentDayEnd) {
                $nextPeriod = min($endTime, $currentDayEnd);
                $interval = $current->diff($nextPeriod);
                $dayHours += $interval->h + ($interval->i / 60);
            } else {
                $nextPeriod = ($current < $currentDayStart) ? min($endTime, $currentDayStart) : min($endTime, (clone $currentDayStart)->modify('+1 day'));
                $interval = $current->diff($nextPeriod);
                $nightHours += $interval->h + ($interval->i / 60);
            }

            $current = $nextPeriod;
        }

        return ['dayHours' => $dayHours, 'nightHours' => $nightHours];
    }
}