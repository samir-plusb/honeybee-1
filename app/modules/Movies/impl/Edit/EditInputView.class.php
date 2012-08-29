<?php

class Movies_Edit_EditInputView extends MoviesBaseView
{
    /**
     * Run this view for the html output type.
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $this->setAttribute('_title', 'Movies - Edit');

        $movieItem = $this->getAttribute('item');
        $data = $movieItem->toArray();
        $data['masterRecord']['screenings'] = $this->initScreeningData($movieItem);

        $requiredMedia = array('trailers', 'images', 'galleries');
        foreach ($requiredMedia as $mediaType)
        {
            if (! isset($data['masterRecord']['media'][$mediaType]) || ! is_array($data['masterRecord']['media'][$mediaType]))
            {
                $data['masterRecord']['media'][$mediaType] = array();
            }
        }
        $this->setAttribute('item_data', $data);

        $this->setAttribute(
            'ticket_data', 
            $this->hasAttribute('ticket')
            ? $this->getAttribute('ticket')->toArray()
            : array()
        );

        $widgetData = $this->getWidgets($movieItem);
        $this->registerJsWidgetOptions($widgetData['options']);
        $this->registerClientSideController($widgetData['registration']);

        $this->setBreadcrumb();
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $moduleCrumb = array(
            'text' => 'Filme',
            'link' => $routing->gen('movies.list'),
            'info' => 'Filme - Listenansicht (Anfang)',
            'icon' => 'icon-list'
        );

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'midas.breadcrumbs', array());
        foreach ($breadcrumbs as $crumb)
        {
            if ('icon-pencil' === $crumb['icon'])
            {
                return;
            }
        }
        $breadcrumbs[] = array(
            'text' => 'Film bearbeiten',
            'info' => 'Bearbeitung von Film: ' . $this->getAttribute('item')->getIdentifier(),
            'icon' => 'icon-pencil'
        );
        
        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'midas.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'midas.breadcrumbs');
    }

    /**
     * Register the given widgets to the client side controller.
     */
    protected function registerClientSideController(array $widgets = array())
    {
        $controllerOptions = array(
            'autobind' => TRUE,
            'widgets' => $widgets
        );
        $this->setAttribute(
            'controller_options',
            htmlspecialchars(json_encode($controllerOptions))
        );
    }

    protected function registerJsWidgetOptions(array $widgets = array())
    {
        foreach ($widgets as $attributeName => $widgetOptions)
        {
            $this->setAttribute(
                $attributeName,
                htmlspecialchars(json_encode($widgetOptions))
            );
        }
    }

    /**
     * register widgets by providing: name, type and selector
     * init widgets by providing options below a key you will use in your templates.
     */
    protected function getWidgets(MoviesWorkflowItem $workflowItem)
    {
        $actors = array();
        foreach ($workflowItem->getMasterRecord()->getActors() as $actor)
        {
            $actors[] = array('label' => $actor, 'value' => $actor);
        }
        $directors = array();
        foreach ($workflowItem->getMasterRecord()->getDirector() as $director)
        {
            $directors[] = array('label' => $director, 'value' => $director);
        }
        $widgetOptions = array( // template-attributes for passing options to particular widgets
            'actors_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'movie[actors]',
                'tags' => $actors
            ),
            'director_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'movie[director]',
                'tags' => $directors
            )
        );
        $widgetRegistration = array( // register widgets to client-side controller
            array(
                'name' => 'actors',
                'type' => 'TagsList',
                'selector' => '.widget-actors'
            ),
            array(
                'name' => 'director',
                'type' => 'TagsList',
                'selector' => '.widget-director'
            )
        );
        return array(
            'options' => $widgetOptions,
            'registration' => $widgetRegistration
        );
    }

    protected function initScreeningData(MoviesWorkflowItem $movieItem)
    {
        $finder = MoviesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('movies.list_config')
        ));
        $screenings = array();
        $theaters = array();
        foreach ($finder->findRelatedTheaters($movieItem) as $theaterItem)
        {
            $theaters[$theaterItem->getIdentifier()] = $theaterItem;
        }

        foreach ($movieItem->getMasterRecord()->getScreenings() as $screening)
        {
            $theaterId = $screening['theaterId'];
            if (! isset($theaters[$theaterId]))
            {
                continue;
            }

            $name = $theaters[$theaterId]->getCoreItem()->getName();
            $date = $screening['date'];
            if (! isset($screenings[$name]))
            {
                $screenings[$name] = array(
                    'screenings' => array(),
                    'theater' => array(
                        'name' => $name,
                        'ticket' => $theaters[$theaterId]->getTicketId()
                    )
                );
            }
            if (! isset($screenings[$name]['screenings'][$date]))
            {
                $screenings[$name]['screenings'][$date] = array();
            }
            $screenings[$name]['screenings'][$date][] = $screening;
        }

        return $screenings;
    }
}

?>
