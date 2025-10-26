<?php

class EProjectRequestForVariationCategory extends BaseEProjectRequestForVariationCategory
{
    public static function getRequestForVariationCategories()
    {
        $pdo = EProjectRequestForVariationCategoryTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT id, name
                    FROM " . EProjectRequestForVariationCategoryTable::getInstance()->getTableName() . " 
                    ORDER BY id ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}