<?php

namespace App\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Doctrine filter for multi-tenancy.
 * Automatically filters all queries to include only records for the current company.
 */
class CompanyFilter extends SQLFilter
{
    /**
     * Add a WHERE condition to the SQL query.
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        // Only apply filter to entities that have a company association
        if (!$targetEntity->hasAssociation('company')) {
            return '';
        }

        // Get the company ID from the filter parameters
        try {
            $companyId = $this->getParameter('company_id');
        } catch (\InvalidArgumentException $e) {
            // Filter parameter not set, skip filtering
            return '';
        }
        
        if (empty($companyId)) {
            return '';
        }

        // Get the join column name for the company association
        $companyAssociation = $targetEntity->getAssociationMapping('company');
        $joinColumn = $companyAssociation['joinColumns'][0]['name'] ?? 'company_id';

        // Return the SQL condition to filter by company
        return sprintf('%s.%s = %s', $targetTableAlias, $joinColumn, $companyId);
    }
}

