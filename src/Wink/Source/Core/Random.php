<?php

namespace Wink\Source\Core;

use Wink\Source\Abstracts\AbstractSource;

class Random extends AbstractSource {
    /**
     * @return string
     */
    public static function getName () {
        return 'Random';
    }

    public function getOptions () {
        return [
            'resource' => [
                'name' => 'Resource',
                'type' => 'select',
                'required' => true,
                'options' => self::getResources()
            ]
        ];
    }

    /**
     * @return array [123 => 'Conference Room', ...]
     */
    public static function getResources () {
        return [
            '1' => 'Room A',
            '2' => 'Room B',
            '3' => 'Room C',
        ];
    }

    /**
     * @return array ['title' => '...', 'subtitle' => '...', ...]
     */
    public function getResource () {
        $resourceId = $this->runtimeConfig['resource'];

        return [
            '1' => [
                'title' => 'Room A',
                'subtitle' => 'Study Room'
            ],
            '2' => [
                'title' => 'Room B',
                'subtitle' => 'Study Room'
            ],
            '3' => [
                'title' => 'Room C',
                'subtitle' => 'Study Room'
            ]
        ][$resourceId];
    }

    /**
     * @return array [['title' => '...', 'start_time' => date('Y-m-d H:i:s'), 'end_time' => date('Y-m-d H:i:s'), [...], ...]
     */
    public function getSchedule () {
        $titles = $this->getTitles();

        $items = [];
        $time = strtotime('today 07:00:00');
        $endTime = strtotime('today 23:00:00');
        $noonTime = strtotime('today 14:00:00');

        while ($time < $endTime) {
            $startTimeFmt = date('Y-m-d H:i:s', $time);

            $time = strtotime('+' . rand(1, 4) * 30 . ' min', $time);

            if ($time > $endTime) {
                $time = $endTime;
            }

            $endTimeFmt = date('Y-m-d H:i:s', strtotime('-1 min', $time));

            if (abs($noonTime - $time) < 3600 * 4 || rand(0, 10) == 0) {
                $items [] = [
                    'title' => $titles[rand(0, count($titles) - 1)],
                    'start_time' => $startTimeFmt,
                    'end_time' => $endTimeFmt,
                ];
            }
        }

        return $items;
    }

    /**
     * @return array
     */
    protected function getTitles () {
        return [
            'World of Opportunities',
            'A Whole New World',
            'A Celebration of Success',
            'A Spectrum of Opportunities',
            'Ain’t No Stoppin’ Us Now',
            'All Systems Go',
            'Anything is Possible',
            'Back to the Future',
            'Back On Top',
            'Becoming Agents of Change',
            'Be Extraordinary',
            'Beat Competitor',
            'Better and Consistent',
            'Beyond All Limits',
            'Board Break Experience at the event!',
            'Breakthrough to Excellence',
            'Breaking Barriers',
            'Breaking Down Barriers',
            'Breaking Out of Your Shell',
            'Breakthrough Performance',
            'Breakthrough to Excellence',
            'Building on the Best',
            'Building for the Future',
            'California Dreamin’',
            'Commitment to Excellence',
            'Creating Customer Connections',
            'Creating Connections-Building Bridges... Together',
            'Challenge Yourself',
            'Charting the Course',
            'Customer Focus',
            'Discovering Natural Treasures',
            'Dedicated To Your Success',
            'Develop the Possibilities',
            'Discover the Difference',
            'Do Great Things',
            'Don’t Stop Believing',
            'Evolving with our Business',
            'Everything Counts',
            'Expect the Best',
            'Exceeding the Vision',
            'Expanding the Possibilities',
            'Explore the Possibilities',
            'Facing the Future – Together',
            'Facing Forward',
            'Facing the Challenges',
            'Focus on Success',
            'Focus on the Future',
            'Fusing Power and People',
            'Gaining the Edge',
            'Get Momentum',
            'Get Switched On!',
            'Get the Edge',
            'Getting It Done',
            'Getting You Prepared for 201: Good to Great',
            'Got Momentum',
            'Growing your Business',
            'Great Expectations',
            'Guide their Journey: Improving Customer Service',
            'Higher, Faster, Stronger',
            'Historic Proportions',
            'Homecoming 201',
            'It Starts with Us',
            'Igniting Team Spirit',
            'Ingenuity @ Work',
            'Innovate, Integrate, Motivate',
            'Innovation Integration',
            'In It to Win It',
            'Journey to the Top',
            'Keep ‘em Rollin’',
            'Leadership Next: Defying Gravity',
            'Leadership: Share the Vision',
            'Leadership: Precision & Performance',
            'Leadership Challenge',
            'Leadership Conference: Guiding the Way into the 21st Century',
            'Leading the Pack',
            'Leading the Way',
            'Leadership: Sharing the Vision',
            'Legendary Leadership Lessons',
            'Live and Let Live',
            'Lighting the Future',
            'Meeting the Challenge',
            'Making a Difference',
            'Make it Happen Make it Matter!',
            'Make Every Connection Matter!',
            'Millennium: Honouring the Past, Treasuring the Present, Shaping the Future',
            'Moving to Mastery',
            'Make the Member Connection-Mission Possible',
            'New Economy Efficiencies/Old Economy Relationships',
            'Next Generation Leadership',
            'Next Level',
            'Navigating the Future',
            'Operation Excellence',
            'People, Process & Performance',
            'Peak Performance',
            'Partners in Excellence',
            'Prism of Possibilities',
            'People, Purpose & Passion:  The Pathway to Success',
            'Pump Up Your Sales Success',
            'Partners in Progress',
            'Performance Driven',
            'Pride and Performance',
            'Play to Win',
            'Portraits of Success',
            'Power of the Past – Force of the Future',
            'Peak Performance',
            'Power Up!',
            'Quality Connections Ready, Set, Grow',
            'Rev Up Your Business Selling Beyond Price',
            'Sharing Solutions',
            'Share the Vision',
            'Shaping the Future',
            'Showtime!',
            'Success Oriented',
            'Shoot for the Stars',
            'Strategies for Success',
            'Success through Synergy',
            'Service You Can Trust',
            'Switch it On',
            'Swing for the Fences',
            'Team Wall-standard',
            'Together Towards Tomorrow',
            'The Challenge of Change',
            'The Power of You',
            'The True Experience',
            'The Pride and the Promise',
            'Takin’ it to the Streets',
            'The Power of Performances',
        ];
    }
}