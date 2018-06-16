<?php

namespace App\Services;

use Symfony\Component\Console\Style\SymfonyStyle;

class DataCollectManager
{

    const ROW = 5;
    const COLUMN = 9;

    /**
     * @var string
     */
    private $fileLocation;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $year;

    /**
     * @var string
     */
    private $month;

    /**
     * @var string
     */
    private $day;

    /**
     * DataCollectManager constructor.
     * @param null $year
     * @param null $month
     * @param null $day
     * @param null $fileLocation
     * @param null $address
     */
    public function __construct($year = null, $month = null, $day = null, $fileLocation = null, $address = null)
    {
        $this->fileLocation = $fileLocation;
        $this->address = $address;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    private function createRequestAddress(\DateTime $date)
    {

    }

    public function getData(SymfonyStyle $io)
    {
        $date = new \DateTime();
        $date->setDate($this->year, $this->month, $this->day);
        $currentDate = new \DateTime();

        $deltaYears = $currentDate->diff($date);

        var_dump($deltaYears->y);

        while ($deltaYears->y != 0 || $deltaYears->m != 0 || $deltaYears->d != 0) {

            $dateFormat = $date->format('dmY');

            $io->text($dateFormat);

            $dailyData = $this->getDailyData($dateFormat);

            //increment section
            $date->add(new \DateInterval('P1D'));
            $deltaYears = $deltaYears = $currentDate->diff($date);
        }


    }

        //https://www.pmu.fr/turf/01042014/R1/C1
        //
        //R = [1...5]
        //C = [1...9]

    public function getDailyData($dateFormat)
    {
        $dailyData = null;
        for ($row = 1; $row < self::ROW; $row++){
            for ($column = 1; $column < self::COLUMN; $column++){
                $this->call($this->address . '/' . $dateFormat . '/R' . $row . '/C' . $column);
            }
        }

        return $dailyData;

    }

    /**
     * @param string $address
     * @return null|string
     */
    public function call($address)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->get($address);

        if we have a valid response
        if ($res->getStatusCode() == 200) {
            return $res->getBody()->getContents();

        } else {
            return null;
        }

    }

    /**
     * @return string
     */
    public function getFileLocation()
    {
        return $this->fileLocation;
    }

    /**
     * @param string $fileLocation
     */
    public function setFileLocation($fileLocation)
    {
        $this->fileLocation = $fileLocation;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }


}