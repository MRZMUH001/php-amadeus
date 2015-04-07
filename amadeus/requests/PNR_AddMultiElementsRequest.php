<?php

namespace Amadeus\requests;


use Amadeus\Client;
use Amadeus\models\AgentCommissions;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\OrderFlow;
use Amadeus\models\Passenger;
use Amadeus\models\PassengerCollection;
use Amadeus\replies\PNR_AddMultiElementsReply;

class PNR_AddMultiElementsRequest extends Request
{

    /** @var bool Flag to just close PNR */
    private $_finalize = false;

    /** @var PassengerCollection */
    private $_passengers;

    /** @var  FlightSegmentCollection */
    private $_segments;

    /** @var  string */
    private $_email;

    /** @var  string */
    private $_phoneNumber;

    /** @var  string */
    private $_validatingCarrier;

    /** @var  AgentCommissions */
    private $_commission;

    /** @var string[] */
    private $_remarks = [
        'INTERNET BOOKING'
    ];

    /**
     * @return boolean
     */
    public function isFinalize()
    {
        return $this->_finalize;
    }

    /**
     * @param boolean $finalize
     */
    public function setFinalize($finalize)
    {
        $this->_finalize = $finalize;
    }

    /**
     * @return \string[]
     */
    public function getRemarks()
    {
        return $this->_remarks;
    }

    /**
     * @param \string $remark
     */
    public function addRemark($remark)
    {
        $this->_remarks[] = $remark;
    }

    /**
     * @return PassengerCollection
     */
    public function getPassengers()
    {
        return $this->_passengers;
    }

    /**
     * @param PassengerCollection $passengers
     */
    public function setPassengers($passengers)
    {
        $this->_passengers = $passengers;
    }

    /**
     * @return FlightSegmentCollection
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * @param FlightSegmentCollection $segments
     */
    public function setSegments($segments)
    {
        $this->_segments = $segments;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->_email = $email;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->_phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->_phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getValidatingCarrier()
    {
        return $this->_validatingCarrier;
    }

    /**
     * @param string $validatingCarrier
     */
    public function setValidatingCarrier($validatingCarrier)
    {
        $this->_validatingCarrier = $validatingCarrier;
    }

    /**
     * @return AgentCommissions
     */
    public function getCommission()
    {
        return $this->_commission;
    }

    /**
     * @param AgentCommissions $commission
     */
    public function setCommission($commission)
    {
        $this->_commission = $commission;
    }

    /**
     * Create from orderflow.
     *
     * @param OrderFlow $orderFlow
     *
     * @return PNR_AddMultiElementsRequest
     */
    public static function createFromOrderFlow(OrderFlow $orderFlow)
    {
        $request = new self();
        $request->setSegments($orderFlow->getSegments());
        $request->setCommission($orderFlow->getCommissions());
        $request->setPassengers($orderFlow->getPassengers());
        $request->setValidatingCarrier($orderFlow->getValidatingCarrier());
        $request->setEmail($orderFlow->getClientEmail());
        $request->setPhoneNumber($orderFlow->getClientPhone());

        return $request;
    }

    /**
     * @param Client $client
     * @return PNR_AddMultiElementsReply
     * @throws \Exception
     */
    public function send(Client $client)
    {
        if (!$this->_finalize) {
            if ($this->_passengers == null || $this->_passengers->count() == 0)
                throw new \Exception("Passengers not specified");
            if ($this->_validatingCarrier == null)
                throw new \Exception("Validating carrier not specified");
            if ($this->_commission == null)
                throw new \Exception("Commissions not specified");
            if ($this->_segments == null)
                throw new \Exception("Segments not specified");
        }

        $params = [];

        # Option codes:
        # 0 - Save and close PNR
        # ET => 10 - Save and leave open
        # ER => 11 - Different types of save with notification in segments status change

        if ($this->_finalize) {
            $params['pnrActions']['optionCode'] = 11;
        } else {
            $params['pnrActions']['optionCode'] = 10;

            foreach ($this->_passengers->getAdults() as $adult) {
                $adultData = [
                    'elementManagementPassenger' => [
                        'reference' => [
                            'qualifier' => 'PR',
                            'number' => $adult->getIndex(),
                        ],
                        'segmentName' => 'NM',
                    ],
                    'passengerData' => [
                        'travellerInformation' => [
                            'traveller' => [
                                'quantity' => $adult->getAssociatedInfant() == null ? 1 : 2,
                                'surname' => $adult->getLastName(),
                            ],
                            'passenger' => [
                                'firstName' => $adult->getFirstNameWithCode(),
                            ],
                        ],
                    ],
                ];

                if ($adult->getAssociatedInfant() != null) {
                    $infant = $adult->getAssociatedInfant();
                    $adultData['passengerData']['travellerInformation']['passenger']['infantIndicator'] = 3;
                    $adultData['passengerData'] = [
                        $adultData['passengerData'],
                        [
                            'travellerInformation' => [
                                'traveller' => [
                                    'surname' => $infant->getLastName(),
                                ],
                                'passenger' => [
                                    'firstName' => $infant->getFirstNameWithCode(),
                                    'type' => 'INF',
                                ],
                            ],
                            'dateOfBirth' => [
                                'dateAndTimeDetails' => [
                                    'date' => strtoupper($infant->getBirthday()->format('dMy')),
                                ],
                            ],
                        ],
                    ];
                }

                $params['travellerInfo'][] = $adultData;
            }

            foreach ($this->_passengers->getChildren() as $child) {
                $childData = [
                    'elementManagementPassenger' => [
                        'reference' => [
                            'qualifier' => 'PR',
                            'number' => $child->getIndex(),
                        ],
                        'segmentName' => 'NM',
                    ],
                    'passengerData' => [
                        'travellerInformation' => [
                            'traveller' => [
                                'quantity' => 1,
                                'surname' => $child->getLastName(),
                            ],
                            'passenger' => [
                                'firstName' => $child->getFirstNameWithCode(),
                                'type' => 'CHD',
                            ],
                        ],
                        'dateOfBirth' => [
                            'dateAndTimeDetails' => [
                                'date' => strtoupper($child->getBirthday()->format('dMy')),
                            ],
                        ],
                    ],
                ];

                $params['travellerInfo'][] = $childData;
            }

            //Additional details
            $params['dataElementsMaster']['marker1'] = null;

            //Unknown
            $params['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'RF',
                ],
                'freetextData' => [
                    'longFreetext' => 'WS',
                ],
            ];

            //TK
            $params['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'TK',
                ],
                'ticketElement' => [
                    'passengerType' => 'PAX',
                    'ticket' => [
                        'indicator' => 'XL',
                        'date' => $this->_segments->getFirstSegment()->getDepartureDate()->format('dmy'),
                        'time' => str_replace(':', '', $this->_segments->getFirstSegment()->getDepartureTime()),
                    ],
                ],
            ];

            //Validating carrier
            $params['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'FV',
                ],
                'ticketingCarrier' => [
                    'carrier' => [
                        'airlineCode' => $this->_validatingCarrier,
                    ],
                ],
            ];

            //Phone number
            if ($this->_phoneNumber != null) {
                $params['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'AP'
                    ],
                    'freetextData' => [
                        'freetextDetail' => [
                            'subjectQualifier' => 3,
                            'type' => 3
                        ],
                        'longFreetext' => $this->_phoneNumber
                    ]
                ];
                $params['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'OS'
                    ],
                    'freetextData' => [
                        'freetextDetail' => [
                            'subjectQualifier' => 3,
                            'type' => 28,
                            'companyId' => 'YY'
                        ],
                        'longFreetext' => 'CTCP' . preg_replace('/\D/', '', $this->_phoneNumber) . '-M'
                    ]
                ];
            }

            //Email
            if ($this->_email != null) {
                $params['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'AP'
                    ],
                    'freetextData' => [
                        'freetextDetail' => [
                            'subjectQualifier' => 3,
                            'type' => 'P02'
                        ],
                        'longFreetext' => $this->_email
                    ]
                ];
            }

            //Form of payment = cash
            $params['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'FP',
                ],
                'formOfPayment' => [
                    'fop' => [
                        'identification' => 'CA',
                    ],
                ],
            ];

            //Remarks
            foreach ($this->_remarks as $remark) {
                $params['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'RM'
                    ],
                    'miscellaneousRemark' => [
                        'remarks' => [
                            'type' => 'RM',
                            'freetext' => strtoupper($remark)
                        ]
                    ]
                ];
            }

            //Agent commission
            //TODO: Add by passenger
//            if ($agentCommission != null) {
//                $data = [
//                    'elementManagementData' => [
//                        'segmentName' => 'FM',
//                    ],
//                    'commission' => [
//                        'commissionInfo' => [],
//                    ],
//                ];
//
//                if ($agentCommission->isPercentage()) {
//                    $data['commission']['commissionInfo']['percentage'] = $agentCommission->getPercent() . '%';
//                } else {
//                    $data['commission']['commissionInfo']['amount'] = $agentCommission->getAmount();
//                }
//
//                $params['dataElementsMaster']['dataElementsIndiv'][] = $data;
//            }

            foreach (array_merge($this->_passengers->getAdults(), $this->_passengers->getChildren()) as $passenger) {
                /* @var Passenger $passenger */
                $dataElement = [
                    'elementManagementData' => [
                        'segmentName' => 'SSR',
                    ],
                    'serviceRequest' => [
                        'ssr' => [
                            'type' => 'DOCS',
                            'status' => 'HK',
                            'quantity' => 1,
                            'companyId' => 'YY',
                            'freetext' => substr($passenger->ssrDocsText(), 0, 70),
                        ],
                    ],
                    'referenceForDataElement' => [
                        'reference' => [
                            'qualifier' => 'PR',
                            'number' => $passenger->getIndex(),
                        ],
                    ],
                ];

                //Long ssr
                if (strlen($passenger->ssrDocsText()) > 70) {
                    $dataElement['serviceRequest']['ssr']['freetext'] = [
                        substr($passenger->ssrDocsText(), 0, 70),
                        substr($passenger->ssrDocsText(), 70, 70),
                    ];
                }

                $params['dataElementsMaster']['dataElementsIndiv'][] = $dataElement;

                //FOID
                $params['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'SSR'
                    ],
                    'serviceRequest' => [
                        'ssr' => [
                            'type' => 'FOID',
                            'status' => 'HK',
                            'quantity' => 1,
                            'companyId' => $this->_validatingCarrier,
                            'freetext' => 'PP' . $passenger->clearedPassport()
                        ],
                    ],
                    'referenceForDataElement' => [
                        'reference' => [
                            'qualifier' => 'PR',
                            'number' => $passenger->getIndex()
                        ]
                    ]
                ];

                $params['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'FE'
                    ],
                    'fareElement' => [
                        'generalIndicator' => 'E',
                        'freetextLong' => $this->_validatingCarrier . " ONLY PSPT " . $passenger->clearedPassport(),
                    ],
                    'referenceForDataElement' => [
                        'reference' => [
                            'qualifier' => 'PR',
                            'number' => $passenger->getIndex(),
                        ]
                    ]
                ];

                if ($infant = $passenger->getAssociatedInfant()) {
                    $params['dataElementsMaster']['dataElementsIndiv'][] = [
                        'elementManagementData' => [
                            'segmentName' => 'SSR',
                        ],
                        'serviceRequest' => [
                            'ssr' => [
                                'type' => 'RM',
                                'status' => 'HK',
                                'quantity' => 1,
                                'companyId' => 'YY',
                                'freetext' => substr($infant->ssrDocsText(), 0, 70),
                            ],
                        ],
                        'referenceForDataElement' => [
                            'reference' => [
                                'qualifier' => 'PR',
                                'number' => $passenger->getIndex(),
                            ],
                        ],
                    ];

                    $params['dataElementsMaster']['dataElementsIndiv'][] = [
                        'elementManagementData' => [
                            'segmentName' => 'FE',
                        ],
                        'fareElement' => [
                            'generalIndicator' => 'E',
                            'passengerType' => 'INF',
                            'freetextLong' => $this->_validatingCarrier . " ONLY PSPT " . $infant->clearedPassport(),
                        ],
                        'referenceForDataElement' => [
                            'reference' => [
                                'qualifier' => 'PR',
                                'number' => $passenger->getIndex(),
                            ],
                        ],
                    ];
                }
            }
        }

        return $this->innerSend($client, 'PNR_AddMultiElements', $params, PNR_AddMultiElementsReply::class);
    }
}