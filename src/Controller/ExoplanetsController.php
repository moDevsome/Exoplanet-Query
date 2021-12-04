<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\ErrorHandler\Error\FatalError;

use App\Helper\ExoplanetsHelper;
use App\Form\Type\ExoplanetsFilterType;
use App\Form\Type\ExoplanetsTableType;
use Exception;

class ExoplanetsController extends AbstractController
{

    private RequestStack $requestStack;
    private string $workingDirectory;
    private $errors = [];

    private $tableColumns = [
        'pl_name' => 'Planet Name',
        'hostname' =>' Host Name',
        'disc_facility' => 'Discovery Facility',
        'discoverymethod' => 'Discovery Method',
        'disc_year' => 'Discovery Year'
    ];

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->workingDirectory = str_ireplace('src'.DIRECTORY_SEPARATOR.'Controller', '', dirname(__FILE__)).'var'.DIRECTORY_SEPARATOR.'app_files'.DIRECTORY_SEPARATOR;

        // Create the local database if not exist
        if(!is_file($this->workingDirectory.'Exoplanets_localDb.txt')) {

            if(!$this->loadDb()) throw new Exception('The local database could not be load, please check your environnement and the dev.log file.');

        }

    }

    private function loadDb() : bool
    {

        $csvFilePath = $this->workingDirectory.'DB.csv';
        if(!is_file($csvFilePath)) throw new FileNotFoundException('The file "'.$this->workingDirectory.'DB.csv" does not exist, please see the github project page to know how to import the local database.');

        $fs = fopen($csvFilePath, 'r');
        $localDbFullFs = fopen($this->workingDirectory.'Exoplanets_localDb.txt', 'w+');
        $choicesOptions = [
            'discoverymethodChoices' => [],
            'discoveryFacilityChoices' => [],
            'discoveryYearChoices' => []
        ];

        $counter = 0;
        $headerBoundary = FALSE;
        $previousLine = '';
        while (($buffer = fgets($fs, filesize($csvFilePath))) !== false) {

            if($headerBoundary === FALSE) {

                $headerBoundary = preg_match('#pl_name,hostname,default_flag#', $buffer) ? TRUE : FALSE;

            }
            else {

                if($counter === 100000) break;
                $segments = explode(',', $buffer);
                $line = implode(',', [
                    $segments[0],
                    $segments[1],
                    $segments[5],
                    $segments[6],
                    $segments[7],
                ]).PHP_EOL;
                if($line !== $previousLine) {

                    fwrite($localDbFullFs, $line);
                    $previousLine = $line;

                }

                if(!in_array($segments[5], $choicesOptions['discoverymethodChoices'])) $choicesOptions['discoverymethodChoices'][]= $segments[5];
                if(!in_array($segments[6], $choicesOptions['discoveryYearChoices'])) $choicesOptions['discoveryYearChoices'][] = $segments[6];
                if(!in_array($segments[7], $choicesOptions['discoveryFacilityChoices'])) $choicesOptions['discoveryFacilityChoices'][] = $segments[7];

                $counter++;

            }

        }
        fclose($fs);
        fclose($localDbFullFs);

        $localDbFieldChoicesFs = fopen($this->workingDirectory.'Exoplanets_localDb_FieldChoices.json', 'w+');
        fwrite($localDbFieldChoicesFs, json_encode($choicesOptions));
        fclose($localDbFieldChoicesFs);

        return is_file($this->workingDirectory.'Exoplanets_localDb.txt') AND is_file($this->workingDirectory.'Exoplanets_localDb_FieldChoices.json');

    }

    /**
     * Process the filter form
     */
    public function filter(): RedirectResponse
    {

        $request = Request::createFromGlobals();
        $post = $request->request->get('filter', []);
        $session = $this->requestStack->getSession();

        if(count($post) > 0) {

            // Check the CSRF Token
            if(!$this->isCsrfTokenValid('filter_form_token', $post['_filter_form_token'])) {

                $this->addFlash('error','Forbidden request.');

            }
            else {

                $activeFilter = $session->get('exoplanetFilter',  []);
                $currentHostname = $activeFilter['hostname'] ?? '';

                // We check the provided data and we store it in the session
                $filterData = [
                    'pl_name' => (string) filter_var($post['pl_name'], FILTER_SANITIZE_STRING),
                    'hostname' => (string) filter_var($post['hostname'] ?? $currentHostname, FILTER_SANITIZE_STRING), // The var "hostname" is not sent if the field is disabled
                    'disc_facility' => (int) $post['disc_facility'] ?? -1,
                    'discoverymethod' => (int) $post['discoverymethod'] ?? -1,
                    'disc_year' => (int) $post['disc_year'] ?? -1,

                ];
                if(strlen($filterData['pl_name']) === 0
                    AND strlen($filterData['hostname']) === 0
                    AND $filterData['disc_facility'] === -1
                    AND $filterData['discoverymethod'] === -1
                    AND $filterData['disc_year'] === -1) {

                    $this->addFlash('error','Please provide at least 1 parameter to perform the query.');

                }
                $session->set('exoplanetFilter', $filterData);

                $session->set('exoplanetCurrentPage', 1);

            }
        }

        // redirect to the index page
        return new RedirectResponse($request->server->get('REDIRECT_BASE', $this->getParameter('app.redirectbase')), 301);

    }

    /**
     * Process the table form (Row count per page, Ordering AND Pagination)
     */
    public function table(): RedirectResponse
    {

        $request = Request::createFromGlobals();
        $post = $request->request->get('table', []);
        $session = $this->requestStack->getSession();

        if(count($post) > 0) {

            // Check the CSRF Token
            if(!$this->isCsrfTokenValid('table_form_token', $post['_table_form_token'])) {

                $this->addFlash('error','Forbidden request.');

            }
            else {

                $session->set('exoplanetLimit', range(25, 150, 25)[$post['row_count']] ?? 50);
                $session->set('exoplanetCurrentPage', (int) $post['current_page'] ?? 1);
                $session->set('exoplanetOrder', [
                    'col' => array_key_exists($post['current_order_col'], $this->tableColumns) ? $post['current_order_col'] : 'pl_name',
                    'dir' => $post['current_order_dir'] === 'asc' ? 'asc' : 'des'
                ]);

            }

        }

        // redirect to the index page
        return new RedirectResponse($request->server->get('REDIRECT_BASE', $this->getParameter('app.redirectbase')), 301);

    }

    public function index(): Response
    {

        $request = Request::createFromGlobals();
        $session = $this->requestStack->getSession();

        // Get the current query data
        if((int) $request->query->get('clearfilter', 0) === 1) { // if the value is set to 1, we need to clear the query data

            $session->set('exoplanetFilter', []);
            $session->set('exoplanetCurrentPage', 1);
            $activeFilter = [];

        }
        else {

            $activeFilter = $session->get('exoplanetFilter',  []);

        }

        // Clear the hostname query data
        if((int) $request->query->get('clearhostname', 0) === 1) { // if the value is set to 1, we need to clear the hostname query data

            $activeFilter['hostname'] = '';
            $session->set('exoplanetFilter', $activeFilter);

        }

        // Set the filter form data
        $formData = [
            'choices' => ExoplanetsHelper::getFieldChoices($this->workingDirectory),
            'availableHostname' => [],
            'hostnameResetRedirect' => $request->server->get('REDIRECT_BASE').'?clearhostname=1',
            'userStates' => $activeFilter
        ];
        asort( $formData['choices']['discoverymethodChoices'] );
        asort( $formData['choices']['discoveryFacilityChoices'] );
        rsort( $formData['choices']['discoveryYearChoices'] );
        asort( $formData['availableHostname'] );

        // Set the results
        $results = array();
        foreach(explode(PHP_EOL, file_get_contents($this->workingDirectory.'Exoplanets_localDb.txt')) as $record) {

            if(strlen($record) === 0) continue;

            $record = array_combine(['pl_name','hostname','discoverymethod','disc_year','disc_facility'], explode(',', $record));

            // *** Filtering the records ***
            if(count($activeFilter) > 0) {

                if(strlen($activeFilter['pl_name']) > 0) {

                    if(!preg_match('#'.strtolower($activeFilter['pl_name']).'#', strtolower($record['pl_name']))) continue;

                }

                if(strlen($activeFilter['hostname']) > 0) {

                    if(!preg_match('#'.strtolower($activeFilter['hostname']).'#', strtolower($record['hostname']))) continue;

                }

                if(array_key_exists($activeFilter['disc_facility'], $formData['choices']['discoveryFacilityChoices'])) {

                    if($formData['choices']['discoveryFacilityChoices'][$activeFilter['disc_facility']] !== trim($record['disc_facility'])) continue;

                }

                if(array_key_exists($activeFilter['discoverymethod'], $formData['choices']['discoverymethodChoices'])) {

                    if($formData['choices']['discoverymethodChoices'][$activeFilter['discoverymethod']] !== $record['discoverymethod']) continue;

                }

                if(array_key_exists($activeFilter['disc_year'], $formData['choices']['discoveryYearChoices'])) {

                    if($formData['choices']['discoveryYearChoices'][$activeFilter['disc_year']] !== $record['disc_year']) continue;

                }


            }

            $results[] = $record;
            if(!in_array($record['hostname'], $formData['availableHostname'])) $formData['availableHostname'][] = $record['hostname'];

        }
        $totalRecords = count($results);

        // *** Ordering the records ***
        $currentOrder = $session->get('exoplanetOrder', [
            'col' => 'pl_name', 'dir' => 'asc'
        ]);
        if($totalRecords > 0) {

            $orderReferenceArray = array_column($results, $currentOrder['col']); // Extract the value of the ordering column into a new array used for sorting
            if($currentOrder['dir'] === 'asc') asort($orderReferenceArray); // ascending order
            else rsort($orderReferenceArray); // descending order
            $orderReferenceArray = array_combine(range(0, $totalRecords - 1), $orderReferenceArray); // Rearrange the reference array by associating index starting from 0
            foreach($results as $result) {

                $orderOffset = array_search($result[$currentOrder['col']], $orderReferenceArray);
                $orderReferenceArray[$orderOffset] = '0';
                $orderedResults[$orderOffset] = $result;

            }
            $results = $orderedResults;

        }

        // Set the filter form
        $formOptions = ['translation_domain' => FALSE, 'block_name' => 'filter'];
        $filterForm = $this->get('form.factory')->createNamed('filter', ExoplanetsFilterType::class, $formData, $formOptions);

        // Set the table form
        $limits = range(25, 150, 25);
        $activeLimit = $session->get('exoplanetLimit', $limits[1]);
        $totalPage = $activeLimit <= $totalRecords ? (int) ceil($totalRecords / $activeLimit) : 1;
        $currentPage = $session->get('exoplanetCurrentPage', 1);
        if($currentPage > $totalPage) $currentPage = 1;
        $tableFormData = [
            'row_count_choice' => $limits,
            'userStates' => [
                'row_count' => array_search($activeLimit, $limits),
                'current_page' => $currentPage,
                'current_order_col' => $currentOrder['col'],
                'current_order_dir' => $currentOrder['dir']
            ]
        ];
        $tableForm = $this->get('form.factory')->createNamed('table', ExoplanetsTableType::class, $tableFormData, ['translation_domain' => FALSE, 'block_name' => 'table']);
        $recordsIterationOffset = ($currentPage - 1) * $activeLimit;
        if($totalRecords >= $activeLimit) {

            if($currentPage < $totalPage) {

                $recordsIterationLimit = $recordsIterationOffset + $activeLimit;

            }
            else {

                // We are in the last page
                $recordsIterationLimit = $recordsIterationOffset;
                do $recordsIterationLimit++;
                while($recordsIterationLimit < $totalRecords);

            }

        }
        else {

            $recordsIterationLimit = $totalRecords;

        }
        if($currentPage === 1 OR $currentPage === $totalPage) $recordsIterationLimit--; // We subtract 1 because the list starts with 0 on the first page

        // Set the pagination data
        if($currentPage < $totalPage) {

            // Whe show no more tha 22 pages
            if($totalPage > 18) {

                $startPage = $currentPage <= 9 ? 1 : $currentPage - 9;
                if(($currentPage + 9) > $totalPage) {

                    $endPage = $totalPage;

                }
                else {

                    if($currentPage <= 9) $endPage = 18;
                    else $endPage = $currentPage + 9;

                }

            }
            else {

                $startPage = 1;
                $endPage = $totalPage;

            }

        }
        else {

            if($totalPage > 18) {

                $startPage = $currentPage - 18;
                $endPage = $totalPage;

            }
            else {

                $startPage = 1;
                $endPage = $totalPage;

            }

        }

        $currentYear = date('Y');
        return $this->renderForm('exoplanets/index.html.twig', [
            'controller_name' => 'ExoplanetsController',
            'page_title' => 'Exoplanets List / Page '.$currentPage,
            'filter_form' => $filterForm,
            'filter_state' => count($activeFilter) === 0 ? ' no-filter' : '',
            'filter_reset_redirect' => $request->server->get('REDIRECT_BASE').'?clearfilter=1',
            'table_form' => $tableForm,
            'current_order' => $currentOrder,
            'current_page' => $currentPage,
            'start_page' => $startPage,
            'end_page' => $endPage,
            'total_page' => $totalPage,
            'columns' => $this->tableColumns,
            'records' => $results,
            'records_iteration_offset' => $recordsIterationOffset,
            'records_iteration_limit' => $recordsIterationLimit,
            'total_records' => $totalRecords,
            'copyright_date' => $currentYear === '2021' ? '2021' : '2021 - '.$currentYear
        ]);
    }

}
