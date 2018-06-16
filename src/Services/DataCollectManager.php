<?php

namespace App\Services;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

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

    private $internalCounter;

    /**
     * @return string
     */
    public function getFileLocation(): string
    {
        return $this->fileLocation;
    }

    /**
     * @param string $fileLocation
     */
    public function setFileLocation(string $fileLocation)
    {
        $this->fileLocation = $fileLocation;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @param string $year
     */
    public function setYear(string $year)
    {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getMonth(): string
    {
        return $this->month;
    }

    /**
     * @param string $month
     */
    public function setMonth(string $month)
    {
        $this->month = $month;
    }

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->day;
    }

    /**
     * @param string $day
     */
    public function setDay(string $day)
    {
        $this->day = $day;
    }

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

    /**
     * @param SymfonyStyle $io
     */
    public function getData(SymfonyStyle $io)
    {
        $date = new \DateTime();
        $date->setDate($this->year, $this->month, $this->day);
        $currentDate = new \DateTime();

        $deltaYears = $currentDate->diff($date);
        while ($deltaYears->y != 0 || $deltaYears->m != 0 || $deltaYears->d != 0) {

            $dateFormat = $date->format('dmY');

            //returns filename => jsonData
            $dailyData = $this->getDailyData($dateFormat, $io);

            $dateFormat2 = $date->format('Ymd');
            $filesystem = new Filesystem();
            $filesystem->mkdir(__DIR__.'\\'.$dateFormat2);
            $this->saveDailyData($dailyData, $dateFormat, $io);

            //increment section
            $date->add(new \DateInterval('P1D'));
            $deltaYears = $deltaYears = $currentDate->diff($date);

        }

    }

    //R = [1...5]
    //C = [1...9]
    /**
     * @param string $dateFormat
     * @param SymfonyStyle $io
     * @return array
     */
    public function getDailyData($dateFormat, SymfonyStyle $io)
    {
        if ($this->internalCounter == null){
            $this->internalCounter = 1;
        }else{
            $this->internalCounter++;
        }
        $io->section($this->internalCounter.' - Retrieving daily data for ' . $dateFormat);
        $dailyData = [];
        $io->progressStart();
        for ($row = 1; $row < self::ROW; $row++){
            for ($column = 1; $column < self::COLUMN; $column++){
                //first call
                $dailyData[$this->urlToFilename($this->address) . '-' . $dateFormat . '-R' . $row . '-C' . $column] = $this->call($this->address . '/' . $dateFormat . '/R' . $row . '/C' . $column);

                //second call
                $secondAddress ='participants';
                $dailyData[$this->urlToFilename($this->address) . '-' . $dateFormat . '-R' . $row . '-C' . $column . $secondAddress] = $this->call($this->address . '/' . $dateFormat . '/R' . $row . '/C' . $column . '/' . $secondAddress);
                $io->progressAdvance();
            }
        }
        $io->progressFinish();
        return $dailyData;
    }

    /**
     * @param string $url
     * @return string
     */
    public function urlToFilename($url)
    {
        return preg_replace('/[\/,.:?]*/i', '', $url);
    }

    /**
     * @param string $address
     * @return null|string
     */
    public function call($address)
    {
        $client = new \GuzzleHttp\Client();
        try{

            $res = $client->get($address);
        }catch(\Exception $e){
            return $e->getMessage();
        }

//      //if we have a valid response
        if ($res->getStatusCode() == 200) {
            return $res->getBody()->getContents();

        } else {
            return null;
        }

    }

    /**
     * @param array $dailyData
     * @param string $folderName
     * @param SymfonyStyle $io
     */
    public function saveDailyData($dailyData, $folderName, SymfonyStyle $io)
    {
        $io->success('Saving daily data for '.$folderName);
        $location = __DIR__;
        foreach ($dailyData as $fileName => $data){
            $this->saveToJson($location . '\\' . $folderName . '\\' . $fileName, $data);
        }
    }

    /**
     * @param string $location
     * @param string $data
     */
    public function saveToJson($location, $data)
    {
        $location = $location . ".json";
        $filesystem = new Filesystem();
        $filesystem->dumpFile($location, $data);
    }
}
