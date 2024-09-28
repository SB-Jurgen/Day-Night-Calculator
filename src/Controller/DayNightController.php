<?php
namespace App\Controller;

use App\Form\Time;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DayNightController extends AbstractController
{
    private const string DAY_START = '06:00';
    private const string DAY_END = '22:00';
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
            $this->addFlash(
                'warning',
                'Please insert times first!'
            );

            return $this->redirectToRoute('index');
        }

        return $this->render('day_night/success.html.twig', [
            'day_hours' => $dayHours,
            'night_hours' => $nightHours,
        ]);
    }

    /**
     * @param DateTimeImmutable $startTime
     * @param DateTimeImmutable $endTime
     * @return float[]|int[]
     */
    private function getDayNightHours(DateTimeImmutable $startTime, DateTimeImmutable $endTime): array
    {
        $now = new DateTimeImmutable();
        $startTime = $startTime->setDate($now->format('Y'), $now->format('m'), $now->format('d'));
        $endTime = $endTime->setDate($now->format('Y'), $now->format('m'), $now->format('d'));
        $dayStart = DateTimeImmutable::createFromFormat('H:i', self::DAY_START, new DateTimeZone('Europe/Tallinn'))
            ->setDate($now->format('Y'), $now->format('m'), $now->format('d'));
        $dayEnd = DateTimeImmutable::createFromFormat('H:i', self::DAY_END, new DateTimeZone('Europe/Tallinn'))
            ->setDate($now->format('Y'), $now->format('m'), $now->format('d'));

        if ($endTime <= $startTime) {
            $endTime = $endTime->modify('+1 day');
        }

        $dayHours = 0;
        $nightHours = 0;

        while ($startTime < $endTime) {
            $currentDayStart = $dayStart->setDate($startTime->format('Y'), $startTime->format('m'), $startTime->format('d'));
            $currentDayEnd = $dayEnd->setDate($startTime->format('Y'), $startTime->format('m'), $startTime->format('d'));

            if ($startTime >= $currentDayStart && $startTime < $currentDayEnd) {
                $nextPeriod = min($endTime, $currentDayEnd);
                $interval = $startTime->diff($nextPeriod);
                $dayHours += $interval->h + ($interval->i / 60);
            } else {
                $nextPeriod = ($startTime < $currentDayStart) ? min($endTime, $currentDayStart) : min($endTime, $currentDayStart->modify('+1 day'));
                $interval = $startTime->diff($nextPeriod);
                $nightHours += $interval->h + ($interval->i / 60);
            }

            $startTime = $nextPeriod;
        }

        return ['dayHours' => $dayHours, 'nightHours' => $nightHours];
    }
}